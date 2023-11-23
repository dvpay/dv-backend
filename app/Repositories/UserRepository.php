<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findAdminByEmail(string $email): ?User
    {
        return User::where('email', $email)->where('is_admin', true)->first();
    }

    public function setGoogle2faSecret(string|null $token, User $user): bool
    {
        return $user->update([
            'google2fa_secret' => $token,
            'google2fa_status' => is_null($token) ? false : $user->google2fa_status
        ]);
    }


}