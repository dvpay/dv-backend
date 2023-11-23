<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Models\StoreDto;
use App\Dto\Models\UserDto;
use App\Enums\UserTokenType;
use App\Exceptions\ApiException;
use App\Facades\Settings;
use App\Http\Requests\Store\UpdateRateSourceRequest;
use App\Http\Requests\User\ChangeGoogle2faRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\SetPasswordRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\VerifyEmailRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\User\TokenResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Registration\RegistrationService;
use App\Services\Store\StoreService;
use App\Services\User\UserService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * UserController
 */
class UserController extends ApiController
{
    /**
     * @param UserService $userService
     * @param AuthService $authService
     * @param RegistrationService $registrationService
     */
    public function __construct(
        private readonly UserService         $userService,
        private readonly AuthService         $authService,
        private readonly RegistrationService $registrationService,
        private readonly StoreService        $storeService,
        private readonly Connection          $db,
    )
    {
    }

    /**
     * @param RegisterRequest $request
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function register(RegisterRequest $request): DefaultResponseResource
    {
        if (!Settings::get('registration_enable')) {
            throw new ApiException(__('Registration Disabled'), 400);
        }

        $dto = new UserDto($request->validated());

        $this->registrationService->handle($dto);

        return (new DefaultResponseResource([]));
    }

    /**
     * @param LoginRequest $request
     * @return TokenResource
     */
    public function login(LoginRequest $request): TokenResource
    {
        $input = $request->validated();

        $token = $this->authService->login($input['email'], $input['password'], $input['googleCode'] ?? '');

        return new TokenResource($token);
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    public function logout(Request $request): DefaultResponseResource
    {
        $user = $request->user();

        $this->authService->logout($user);

        return (new DefaultResponseResource([]));
    }

    /**
     * @param ResetPasswordRequest $request
     * @return DefaultResponseResource
     */
    public function resetPassword(ResetPasswordRequest $request): DefaultResponseResource
    {
        $this->userService->resetPassword($request->get('email'));

        return (new DefaultResponseResource([]));
    }

    /**
     * @param SetPasswordRequest $request
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function setPassword(SetPasswordRequest $request): DefaultResponseResource
    {
        if (!$this->userService->updatePassword($request->get('token'), $request->get('password'))) {
            throw new NotFoundHttpException(__("Can't set new password"));
        }

        return (new DefaultResponseResource([]));
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    public function detail(Request $request): DefaultResponseResource
    {
        $user = $request->user();

        $result = $this->userService->getDetailInfo($user);

        return new DefaultResponseResource($result);
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function update(UpdateRequest $request): DefaultResponseResource
    {
        $input = $request->input();
        $user = $request->user();

        $dto = new UserDto([
            'location' => $input['location'] ?? null,
            'language' => $input['language'] ?? null,
            'email'    => $user->hasVerifiedEmail() ? null : $request->input('email'),
            'phone'    => $input['phone'] ?? null,
        ]);

        $this->userService->update($user, $dto);

        return new DefaultResponseResource([]);
    }

    public function getRateSource(Authenticatable $user): DefaultResponseResource
    {
        $store = $user->storesHolder()->first();

        return new DefaultResponseResource([
            'rate_source' => $user->rate_source,
            'rate_scale'  => $store?->rate_scale
        ]);
    }

    /**
     * @throws Throwable
     */
    public function updateRateSource(UpdateRateSourceRequest $request): DefaultResponseResource
    {
        $user = $request->user();

        $dto = new UserDto([
            'rateSource' => $request->input('rateSource'),
        ]);
        $storeDto = new StoreDto([
            'rateSource' => $request->input('rateSource'),
            'rateScale'  => $request->input('rateScale')
        ]);

        try {
            $this->db->beginTransaction();

            $this->userService->update($user, $dto);
            $this->storeService->batchUpdateStore($storeDto, $user);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        };

        return new DefaultResponseResource([]);
    }

    /**
     * @param ChangePasswordRequest $request
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function changePassword(ChangePasswordRequest $request): DefaultResponseResource
    {
        $user = $request->user();

        $this->userService->changePassword($user, $request->input('newPassword'), $request->input('oldPassword'));
        return new DefaultResponseResource([]);
    }

    /**
     * @throws Throwable
     */
    public function activate(VerifyEmailRequest $request): TokenResource
    {
        $user = User::where('id', $request->input('id'))
            ->firstOrFail();

        if (!$this->userService->verifyEmail(user: $user, tokenText: $request->input('hash'))) {
            throw new NotFoundHttpException(__('Token not found or expiry'));
        }

        $token = $user->createToken('user', [UserTokenType::Login->value]);

        return new TokenResource($token->plainTextToken);
    }

    /**
     * @param Request $request
     * @return DefaultResponseResource
     */
    public function resendEmail(Request $request): DefaultResponseResource
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([__('Your email is already activated')]);
        }

        $user->sendEmailVerificationNotification();

        return new DefaultResponseResource([]);
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function toggle2fa(ChangeGoogle2faRequest $request)
    {
        $user = $request->user();
        $this->authService->setUser2faSecret($user, $request->input('status'));

        return new DefaultResponseResource([]);
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     * @throws Throwable
     */
    public function verify2fa(Request $request)
    {
        $user = $request->user();
        $status = $this->authService->validateKey($user, $request->input('googleCode') ?? '');

        if (!$status) {
            throw ValidationException::withMessages([__('Invalid code')]);
        }


        $dto = new UserDto([
            'google2faStatus' => $status,
        ]);

        $this->userService->update($user, $dto);

        return new DefaultResponseResource([]);
    }
}
