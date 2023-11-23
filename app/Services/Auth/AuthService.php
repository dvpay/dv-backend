<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Enums\UserTokenType;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class AuthService
{
    public function __construct(
        private UserRepository $repository,
        private Google2FA      $google2FA
    )
    {
    }


    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function login(string $email, string $password, string $twoFAKey = ""): string
    {
        if (
            !Auth::attempt([
                'email'    => $email,
                'password' => $password,
            ])
        ) {
            throw new NotFoundHttpException(__('User not found'));
        }
        $user = Auth::user();

        if ($user->google2fa_status) {
            if (empty($twoFAKey)) throw new ApiException(__("Google 2fa key required"), Response::HTTP_UNPROCESSABLE_ENTITY);

            if (!$this->validateKey($user, $twoFAKey)) throw ValidationException::withMessages([__('Invalid code')]);

        }

        $token = $user->createToken('user', [UserTokenType::Login->value]);

        return $token->plainTextToken;
    }

    public function logout(User $user): void
    {
        if (!$user->currentAccessToken()->delete()) {
            throw new BadRequestHttpException(__('Error while logging out'));
        }
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function setUser2faSecret(User $user, bool $status): bool
    {
        $token = $status ? $this->google2FA->generateSecretKey() : null;

        return $this->repository->setGoogle2faSecret($token, $user);
    }

    /**
     * @param User $user
     * @param string $oneTimeCode
     * @return bool|int
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function validateKey(User $user, string $oneTimeCode): bool|int
    {
        return $this->google2FA->verifyKey($user->google2fa_secret, $oneTimeCode);
    }

}
