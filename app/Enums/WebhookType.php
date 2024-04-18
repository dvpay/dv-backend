<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookType: string
{
    case InvoiceCreated = 'InvoiceCreated';
    case PaymentReceived = 'PaymentReceived';
    case InvoiceExpired = 'InvoiceExpired';

    case UnconfirmedTransaction = 'UnconfirmedTransaction';

    public function title(): string
    {
        return match ($this)
        {
            self::InvoiceCreated => 'A new invoice has been created',
            self::PaymentReceived => 'A new payment has been received',
            self::InvoiceExpired => 'An invoice has expired',
            self::UnconfirmedTransaction => 'A new unconfirmed transaction'
        };
    }
}