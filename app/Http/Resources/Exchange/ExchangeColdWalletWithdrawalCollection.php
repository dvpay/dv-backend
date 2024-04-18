<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\ExchangeColdWalletWithdrawal */
class ExchangeColdWalletWithdrawalCollection extends BaseCollection
{
    public $collects = ExchangeColdWalletWithdrawalResource::class;
}
