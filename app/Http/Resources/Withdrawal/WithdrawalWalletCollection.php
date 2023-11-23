<?php

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\WithdrawalWallet */
class WithdrawalWalletCollection extends BaseCollection
{
    public $collects = WithdrawalWalletResource::class;
}
