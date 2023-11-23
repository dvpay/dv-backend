<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\InvoiceStatus;
use App\Models\Payer;

class CreateInvoiceDto extends ArrayDto
{
    public readonly string $slug;
    public readonly InvoiceStatus $status;
    public readonly string $orderId;
    public readonly string $currencyId;
    public readonly string $amount;
    public readonly string $description;
    public readonly string $returnUrl;
    public readonly string $successUrl;
    public readonly string $destination;
    public readonly string $paymentMethod;
    public readonly array $custom;
    public readonly Payer $payer;
}
