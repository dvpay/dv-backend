<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;
use Exception;
use Illuminate\Http\Request;

class ListInvoiceAddressesResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        return [
            'storeId' => $this->storeId,
            'storeName' => $this->storeName,
            'invoiceId' => $this->invoiceId,
            'address' => $this->address,
            'currencyId' => $this->currencyId,
            'balance' => $this->balance,
        ];
    }
}
