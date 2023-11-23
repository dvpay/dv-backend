<?php

namespace App\Http\Resources\Processing;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\Transaction */
class ProcessingWalletStatsCollection extends BaseCollection
{
    public $collects = ProcessingWalletStatsResource::class;
}
