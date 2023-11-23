<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\ExchangeUserPairs */
class UserPairsCollection extends BaseCollection
{
    public $collects = UserPairsResource::class;
}
