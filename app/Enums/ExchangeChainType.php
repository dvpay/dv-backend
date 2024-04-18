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

    public function blockchain(): Blockchain
    {
        return match ($this) {
            self::TRC20USDT => Blockchain::Tron,
            self::ERC20USDT, self::ETH => Blockchain::Ethereum,
            self::BTC => Blockchain::Bitcoin,
        };
    }
}
