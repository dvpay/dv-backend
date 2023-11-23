<?php

namespace App\Enums;

use App\Traits\EnumToArray;
use ReflectionClass;

enum WithdrawalRuleType: string
{
    use EnumToArray;

    case BalanceLimit = 'balance';
    case Interval = 'interval';
    case Manual = 'manual';
    case Deposit = 'deposit';
    case LimitAndInterval = 'limitAndBalance';

    public function getName(): string
    {
        return match ($this) {
            self::BalanceLimit => __('Balance'),
            self::Interval => __('Interval'),
            self::Manual => __('Manual'),
            self::LimitAndInterval => __('LimitAndBalance'),
        };
    }

    public static function getValue(): array
    {
        $reflection = new ReflectionClass(self::class);
        $constants = $reflection->getConstants();
        $keyValuePairs = [];

        foreach ($constants as $key => $value) {
            $keyValuePairs[$key] = $value;
        }

        return $keyValuePairs;
    }
}
