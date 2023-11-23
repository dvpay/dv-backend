<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseCollection;

class ListInvoiceAddressesCollectionNew extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ListInvoiceAddressesResourceNew::class;
}
