<?php

namespace App\Http\Resources\Processing;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\Transaction */
class ProcessingWalletTransferCollection extends BaseCollection
{
    public $collects = ProcessingWalletTransferResource::class;
}
