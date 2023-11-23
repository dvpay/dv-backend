<?php

namespace App\Http\Resources\HotWallet;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\HotWallet */
class HotWalletCollection extends BaseCollection
{
    public $collects = HotWalletResource::class;
}
