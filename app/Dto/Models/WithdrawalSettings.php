<?php

namespace App\Dto\Models;

use App\Dto\ArrayDto;
use App\Enums\ExchangeService;
use App\Enums\WithdrawalRuleType;

class WithdrawalSettings extends ArrayDto
{
    public readonly string $address;
    public readonly string $blockchain;
    public readonly bool $enabled;
    public readonly int $minBalance;
    public readonly int $interval;
    public readonly bool $enableAutomaticExchange;
    public readonly ExchangeService $exchange;
    public readonly string $fromCurrencyId;
    public readonly string $toCurrencyId;

    public function getRules(): array
    {
        return [
            WithdrawalRuleType::BalanceLimit->value => $this->minBalance,
            WithdrawalRuleType::Interval->value => $this->interval,
        ];
    }
}