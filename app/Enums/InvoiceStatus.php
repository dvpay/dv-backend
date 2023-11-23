<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Waiting              = 'waiting';
    case WaitingConfirmations = 'waiting_confirmations';
    case Paid                 = 'paid';
    case PartiallyPaid        = 'partially_paid';
    case PartiallyPaidExpired = 'partially_paid_expired';
    case Expired              = 'expired';
    case Canceled             = 'canceled';
    case Success              = 'success';
    case OverPaid             = 'overpaid';

    public function title(): string
    {
        return match ($this) {
            InvoiceStatus::Waiting => 'Waiting',
            InvoiceStatus::WaitingConfirmations => 'Waiting confirmations',
            InvoiceStatus::Paid => 'Paid',
            InvoiceStatus::PartiallyPaid => 'Partially paid',
            InvoiceStatus::PartiallyPaidExpired => 'Partially paid expired',
            InvoiceStatus::Expired => 'Expired',
            InvoiceStatus::Canceled => 'Canceled',
            InvoiceStatus::Success => 'Success',
            InvoiceStatus::OverPaid => 'Overpaid'
        };
    }

    public function event(): ?WebhookType
    {
        return match ($this) {
            InvoiceStatus::PartiallyPaid, InvoiceStatus::Paid, InvoiceStatus::Success, InvoiceStatus::OverPaid => WebhookType::PaymentReceived,
            InvoiceStatus::PartiallyPaidExpired, InvoiceStatus::Expired => WebhookType::InvoiceExpired,
            InvoiceStatus::Waiting, InvoiceStatus::WaitingConfirmations, InvoiceStatus::Canceled => null,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


    public function getValue(): string
    {
        return $this->value;
    }

}
