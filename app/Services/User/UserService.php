<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Dto\Models\UserDto;
use App\Enums\PermissionsEnum;
use App\Enums\TelegramNotificationStatus;
use App\Enums\UserRole;
use App\Enums\UserTokenType;
use App\Mail\User\ResetPasswordEmail;
use App\Models\Invite;
use App\Models\TgUser;
use App\Models\User;
use App\Services\Processing\Contracts\OwnerContract;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use PragmaRX\Google2FA\Google2FA;
use Throwable;

/**
 * UserService
 */
readonly class UserService
{
    /**
     * @param OwnerContract $owner
     * @param Connection $db
     * @param Hasher $hash
     * @param Mailer $mail
     */
    public function __construct(
        private OwnerContract $owner,
        private Connection    $db,
        private Hasher        $hash,
        private Mailer        $mail,
        private Google2FA     $google2FA,
    )
    {
    }

    /**
     * @param UserDto $dto
     * @return User
     * @throws Throwable
     */
    public function create(UserDto $dto, string $role): User
    {
        try {
            $this->db->beginTransaction();

            $user = User::create([
                'email'    => $dto->email,
                'name'     => $dto->name ?? '',
                'password' => $this->hash->make($dto->password),
                'is_admin' => $dto->isAdmin ?? false,
                'location' => $dto->location ?? null,
            ]);

            $user->processing_owner_id = $this->owner->createOwner(config('app.app_domain') . '-user-' . $user->id);
            $user->save();

            $user->assignRole($role);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }

        event(new Registered($user));

        return $user;
    }

    /**
     * Send confirmation email
     *
     * @param User $user
     * @return void
     */


    /**
     * Verify user email
     *
     * @param string $tokenText
     * @return bool
     * @throws Throwable
     */
    public function verifyEmail(User $user, string $tokenText): bool
    {
        if (!hash_equals($tokenText, sha1($user->getEmailForVerification() . $user->created_at . $user->id))) {
            return false;
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            return true;
        }

        return false;
    }

    /**
     * Checks if token exists and it has some ability
     *
     * @param PersonalAccessToken|null $token
     * @param string $ability
     * @return bool
     */
    public function checkTokenCan(?PersonalAccessToken $token, UserTokenType $ability): bool
    {
        if (!$token) {
            return false;
        }

        if (
            $token->cant($ability)
            || $token->name !== $ability
        ) {
            return false;
        }

        return true;
    }

    /**
     * Update user password
     *
     * @param string $tokenText
     * @param string $password
     * @return bool
     * @throws Throwable
     */
    public function updatePassword(string $tokenText, string $password): bool
    {
        $token = PersonalAccessToken::findToken($tokenText);
        if (!$this->checkTokenCan($token, UserTokenType::ResetPassword->value)) {
            return false;
        }

        if (!$user = $token->tokenable) {
            return false;
        }
        try {
            $this->db->beginTransaction();
            $user->password = $this->hash->make($password);

            if (!$user->save()) {
                $this->db->rollBack();
                return false;
            }

            if (!$this->revokeAllTokens($user)) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            return false;
        }
    }


    /**
     * Revoke all user tokens
     *
     * @param User $user
     * @return bool
     */
    public static function revokeAllTokens(User $user): bool
    {
        return (bool)$user->tokens()->delete();
    }

    /**
     * Send confirmation Email to reset user password
     *
     * @param string $email
     * @return bool
     */
    public function resetPassword(string $email): bool
    {
        $user = User::whereEmail($email)->first();

        $token = $user->createToken(UserTokenType::ResetPassword->name, [UserTokenType::ResetPassword->value]);

        $this->mail->to($user->email)
            ->queue(new ResetPasswordEmail($user, $token->plainTextToken));

        return true;
    }

    public function getDetailInfo(User $user): array
    {
        $telegramNotification = TelegramNotificationStatus::Disabled->value;
        $tgUser = TgUser::where('user_id', $user->id)->first();
        if ($tgUser) {
            $telegramNotification = TelegramNotificationStatus::Enabled->value;
        }

        $url = $user->google2fa_secret
            ? $this->google2FA->getQRCodeUrl(config('app.app_domain'), $user->email, $user->google2fa_secret)
            : null;

        return [
            'email'                => $user->email,
            'roles'                => $user->getRoleNames(),
            'permissions'          => $user->getPermissionNames(),
            'telegramNotification' => $telegramNotification,
            'location'             => $user->location,
            'language'             => $user->language,
            'phone'                => $user->phone,
            'isEmailVerified'      => $user->hasVerifiedEmail(),
            'google2faSecret'      => $user->google2fa_secret,
            'google2faUrl'         => $url,
            'google2faStatus'      => $user->google2fa_status,
            'permission'           => [
                'withdrawal'   => $user->hasPermissionTo(PermissionsEnum::TransfersFunds->value),
                'storePay'     => $user->hasPermissionTo(PermissionsEnum::StopStorePay->value),
                'stopExchange' => $user->hasPermissionTo(PermissionsEnum::ExchangeStop->value)
            ]
        ];
    }

    /**
     * @throws Throwable
     */
    public function update(User $user, UserDto $dto): void
    {
        $user->location = $dto->location ?? $user->location;
        $user->language = $dto->language ?? $user->language;
        $user->rate_source = $dto->rateSource ?? $user->rate_source;
        $user->email = $dto->email ?? $user->email;
        $user->phone = $dto->phone ?? $user->phone;
        $user->google2fa_status = $dto->google2faStatus ?? $user->google2fa_status;

        $user->saveOrFail();
    }

    public function getAllRoot()
    {
        return User::select('id')
            ->role(UserRole::Root->value)
            ->with(['telegram', 'notificationTarget'])
            ->get();
    }

    /**
     * @param User $user
     * @param string $newPassword
     * @param string $oldPassword
     * @return User
     * @throws Throwable
     */
    public function changePassword(User $user, string $newPassword, string $oldPassword): User
    {
        if (!$this->hash->check($oldPassword, $user->getAuthPassword())) {
            throw ValidationException::withMessages([__('validation.current_password')]);
        }

        $user->update([
            'password' => $this->hash->make($newPassword)
        ]);

        $user->tokens()->where('id', '!=', $user->currentAccessToken()?->id)->delete();

        return $user;
    }


    /**
     * @return User|LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator|
     */
    public function getAllUser(Request $request): LengthAwarePaginator|User|\Illuminate\Pagination\LengthAwarePaginator
    {
        return User::with('roles')
            ->with('stores')
            ->with('storesHolder')
            ->paginate($request->input('perPage'));
    }

    /**
     * @param User $user
     * @param array $roles
     * @return User
     */
    public function updateUserRoles(User $user, array $roles): User
    {
        return $user->syncRoles($roles);
    }


    /**
     * @param User $user
     * @return User
     * @throws Throwable
     */
    public function deleteUser(User $user): User
    {
        try {
            $this->db->beginTransaction();

            $user->tokens()->delete();
            $user->roles()->detach();
            $user->delete();

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $user;
    }

    /**
     * @param User $user
     * @param Request $request
     * @return LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator|array
     */
    public function getInvitedByUser(User    $user,
                                     Request $request
    ): LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator|array
    {
        return Invite::where('user_id', $user->id)
            ->with('user.roles')
            ->paginate($request->input('perPage'));
    }
}
