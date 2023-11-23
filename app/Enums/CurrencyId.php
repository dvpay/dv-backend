<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Currency ids
 */
enum CurrencyId: string
{
    case BtcBitcoin = 'BTC.Bitcoin';
    case UsdtTron = 'USDT.Tron';

    case EthEthereum = 'ETH.Ethereum';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getToken($token): self
    {
        return match ($token) {
            "btc" => self::BtcBitcoin,
            "usdt" => self::UsdtTron,
            "eth" => self::EthEthereum,
        };
    }

    public static function enabledCurrency(): array
    {
        return [
            self::BtcBitcoin,
            self::UsdtTron
        ];
    }
}

