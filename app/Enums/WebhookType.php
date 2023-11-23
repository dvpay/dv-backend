<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookType: string
{
    case InvoiceCreated = 'InvoiceCreated';
    case PaymentReceived = 'PaymentReceived';
    case InvoiceExpired = 'InvoiceExpired';

    public function title(): string
    {
        return match ($this)
        {
            WebhookType::InvoiceCreated => 'A new invoice has been created',
            WebhookType::PaymentReceived => 'A new payment has been received',
            WebhookType::InvoiceExpired => 'An invoice has expired',
        };
    }
}