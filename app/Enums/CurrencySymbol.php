<?php

declare(strict_types=1);

namespace App\Enums;

enum CurrencySymbol: string
{
    case BTC = 'BTC';
    case USD = 'USD';
    case USDT = 'USDT';
    case TRX = 'TRX';
    case ETH = 'ETH';
}