<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WithdrawalWallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WithdrawalWalletPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        
    }

    public function view(User $user, WithdrawalWallet $withdrawalWallet): bool
    {
        return $user->id === $withdrawalWallet->user_id;
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, WithdrawalWallet $withdrawalWallet): bool
    {
        return $user->id === $withdrawalWallet->user_id;
    }

    public function delete(User $user, WithdrawalWallet $withdrawalWallet): bool
    {
    }

    public function restore(User $user, WithdrawalWallet $withdrawalWallet): bool
    {
    }

    public function forceDelete(User $user, WithdrawalWallet $withdrawalWallet): bool
    {
    }
}
