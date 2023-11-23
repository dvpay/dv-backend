<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\ExchangeWalletCurrency */
class WalletCurrencyCollection extends BaseCollection
{
    public $collection = WalletCurrencyResource::class;
}
