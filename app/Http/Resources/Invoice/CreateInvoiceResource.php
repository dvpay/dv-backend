<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;

class CreateInvoiceResource extends BaseResource
{
    public function __construct(
        $resource,
        private readonly string $url = ''
    )
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'paymentUrl' => $this->url . '/' . $this->id,
            'invoiceId' => $this->id,
        ];
    }
}