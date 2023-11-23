<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\HotWallet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HotWalletPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Root->value, UserRole::Admin->value);
    }

    public function view(User $user, HotWallet $hotWallet): bool
    {
        return $user->id === $hotWallet->user_id;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, HotWallet $hotWallet): bool
    {
    }

    public function delete(User $user, HotWallet $hotWallet): bool
    {
    }

    public function restore(User $user, HotWallet $hotWallet): bool
    {
    }

    public function forceDelete(User $user, HotWallet $hotWallet): bool
    {
    }
}
