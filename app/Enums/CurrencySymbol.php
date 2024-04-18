<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Str;

enum CurrencySymbol: string
{
    case BTC = 'BTC';
    case USD = 'USD';
    case USDT = 'USDT';
    case TRX = 'TRX';
    case ETH = 'ETH';

    public function toLower()
    {
        return Str::lower($this->value);
    }

    public static function casesToLower()
    {
        return array_map( fn($symbol): string => Str::lower($symbol->value), self::cases());
    }

    public static function tryFromLower(string $value)
    {
        return self::tryFrom(Str::upper($value));
    }
}