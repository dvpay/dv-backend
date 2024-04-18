<?php

namespace App\Http\Resources\Payer;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\PayerAddress */
class PayerUndisclosesAddressCollection extends BaseCollection
{
    public $collects = PayerUndisclosesAddressResource::class;
}
