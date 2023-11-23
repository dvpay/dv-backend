<?php

namespace App\Dto;

use App\Enums\ExchangeService;
use App\Enums\WithdrawalRuleType;

/**
 * WithdrawalSettingUpdate DTO.
 */
class WithdrawalSettingUpdate extends ArrayDto
{
    public readonly string $address;
    public readonly string $blockchain;
    public readonly bool $enabled;
    public readonly int $minBalance;
    public readonly int $interval;
    public readonly bool $enableAutomaticExchange;
    public readonly ExchangeService $exchange;
    public readonly array $exchangeCurrencies;
    public bool $exchangeColdWalletIsEnabled = false;
    public ?string $exchangeColdWalletAddress;
    public ?float $exchangeColdWalletMinBalance = 5.00;
    //TODO after update php ^8.2 change to enums value
    public ?string $exchangeChain = 'trc20usdt';
    public ?string $withdrawalIntervalCron;

    /**
     * @return array
     */
    public function getRules(): array
    {
        return [
            WithdrawalRuleType::BalanceLimit->value => $this->minBalance,
            WithdrawalRuleType::Interval->value     => $this->interval,
        ];
    }
}