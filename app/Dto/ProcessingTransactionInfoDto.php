<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enums\Blockchain;

class ProcessingTransactionInfoDto extends ArrayDto
{
    public readonly string $txId;
    public readonly string $amount;
    public readonly string $time;
    public readonly Blockchain $blockchain;
    public readonly string $contractAddress;
    public readonly string $sender;
    public readonly string $receiver;
    public array $watches = [];
    public string $payerId = '';
    public readonly int $confirmations;
}
