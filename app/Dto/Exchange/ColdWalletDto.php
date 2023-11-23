<?php

namespace App\Dto\Exchange;

use App\Dto\ArrayDto;
use App\Enums\CurrencySymbol;

class ColdWalletDto extends ArrayDto
{
    public readonly int $exchangeId;
    public readonly int $userId;
    public readonly bool $isWithdrawalEnable;
    public readonly float $minBalance;
    public readonly string $chain;
    public readonly string $currency;
    public readonly string $address;
}