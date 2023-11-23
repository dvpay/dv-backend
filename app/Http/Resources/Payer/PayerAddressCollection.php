<?php

namespace App\Http\Resources\Payer;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\PayerAddress */
class PayerAddressCollection extends BaseCollection
{
    public $collects = PayerAddressResource::class;
}
