<?php
declare(strict_types=1);

namespace App\Dto\Transfer;

use App\Dto\ArrayDto;
use App\Enums\TransferKind;
use App\Enums\TransferStatus;
use App\Models\Currency;
use App\Models\User;

class TransferDto extends ArrayDto
{
    public string $uuid;
    public User $user;
    public TransferKind $kind = TransferKind::TransferFromAddress;
    public Currency $currency;
    public TransferStatus $status;
    public string $addressFrom;
    public string $addressTo;
    public string $contract;
    public float $amount;
    public float $amountUsd;
}
