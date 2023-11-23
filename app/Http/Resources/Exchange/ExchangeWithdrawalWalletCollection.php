<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\ExchangeWithdrawalWallet */
class ExchangeWithdrawalWalletCollection extends BaseCollection
{
    public $collects = ExchangeWithdrawalWalletResource::class;

}
