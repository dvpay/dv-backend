<?php

namespace App\Enums;

enum ExchangeChainType: string
{
    case TRC20USDT = 'trc20usdt';
    case ERC20USDT = 'usdterc20';

    case BTC = 'btc';

    case ETH = 'eth';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}