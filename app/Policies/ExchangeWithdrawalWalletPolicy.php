<?php

namespace App\Policies;

use App\Models\ExchangeWithdrawalWallet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExchangeWithdrawalWalletPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        
    }

    public function view(User $user, ExchangeWithdrawalWallet $exchangeWithdrawalWallet): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ExchangeWithdrawalWallet $exchangeWithdrawalWallet): bool
    {
    }

    public function delete(User $user, ExchangeWithdrawalWallet $exchangeWithdrawalWallet): bool
    {
        return $user->id === $exchangeWithdrawalWallet->user_id;
    }

    public function restore(User $user, ExchangeWithdrawalWallet $exchangeWithdrawalWallet): bool
    {
    }

    public function forceDelete(User $user, ExchangeWithdrawalWallet $exchangeWithdrawalWallet): bool
    {
    }
}
