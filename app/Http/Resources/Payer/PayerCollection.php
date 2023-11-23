<?php

namespace App\Http\Resources\Payer;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\Payer */
class PayerCollection extends BaseCollection
{
    public $collects = PayerResource::class;
}
