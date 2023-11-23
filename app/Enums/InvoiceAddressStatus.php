<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceAddressStatus: string
{
    case Ready = 'ready';
    case UsedInvoice = 'usedInvoice';
    case LinkedToInvoice = 'linkedToInvoice';
    case Hold = 'hold';

    public function title(): string
    {
        return match ($this)
        {
            InvoiceAddressStatus::Ready => 'Ready to use',
            InvoiceAddressStatus::UsedInvoice => 'Waiting confirmations',
            InvoiceAddressStatus::LinkedToInvoice => 'Paid',
            InvoiceAddressStatus::Hold => 'Partially paid',
        };
    }
}
