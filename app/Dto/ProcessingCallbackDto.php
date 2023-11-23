<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Enums\ProcessingCallbackType;
use App\Enums\TransferStatus;

class ProcessingCallbackDto extends ArrayDto
{
    public readonly string $id;
    public readonly ProcessingCallbackType $type;
    public readonly InvoiceStatus $status;
    public readonly string $tx;
    public readonly string $amount;
    public readonly Blockchain $blockchain;
    public readonly string $address;
    public readonly string $sender;
    public readonly string $contractAddress;
    public readonly string $confirmations;
    public readonly string $time;
    public readonly bool $isManual;
    public readonly string $ownerId;
    public readonly string $invoice_id;
    public readonly string $payer_id;
    public ?string $uuid;
    public readonly TransferStatus $transferStatus;
    public ?int $energy = null;
    public ?int $bandwidth = null;
}
