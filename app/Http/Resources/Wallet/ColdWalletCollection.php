<?php

namespace App\Http\Resources\Wallet;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\ExchangeColdWallet */
class ColdWalletCollection extends BaseCollection
{
    public $collects = ColdWalletResource::class;
}
