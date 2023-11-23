<?php

namespace App\Dto;

use App\Enums\WithdrawalRuleType;

/**
 * WithdrawalSettingGet
 */
class WithdrawalSettingGet extends ArrayDto
{
    public readonly string $address;
    public readonly string $blockchain;
    public readonly bool $enabled;
    public readonly int $minBalance;
    public readonly int $interval;
    public readonly bool $enableAutomaticExchange;
    public readonly string $exchange;
    public readonly array $exchangeCurrencies;
    public bool $exchangeColdWalletIsEnabled = false;
    public ?object $exchangeColdWalletAddresses;
    public ?float $exchangeColdWalletMinBalance = 5.00;

    public ?string $exchangeChain;
    public ?string $withdrawalIntervalCron;

    /**
     * @return array
     */
    public function getRules(): array
    {
        return [
            WithdrawalRuleType::BalanceLimit->value => $this->minBalance,
        ];
    }
}