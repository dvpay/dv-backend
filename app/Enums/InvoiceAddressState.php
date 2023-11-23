<?php

declare(strict_types=1);

namespace App\Enums;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
enum InvoiceAddressState: string
{
    case Free = 'free';
    case Busy = 'busy';
    case Hold = 'hold';

    public function title(): string
    {
        return match ($this)
        {
            InvoiceAddressState::Free => 'Ready to use.',
            InvoiceAddressState::Busy => 'Linked to invoice.',
            InvoiceAddressState::Hold => 'Hold.',
        };
    }
}
