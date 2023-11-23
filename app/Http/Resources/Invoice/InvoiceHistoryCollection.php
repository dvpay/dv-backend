<?php

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\InvoiceHistory */
class InvoiceHistoryCollection extends BaseCollection
{
  public $resource = InvoiceHistoryResource::class;
}
