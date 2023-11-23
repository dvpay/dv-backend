<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Store;

enum RateSource: string
{
    case CoinGate = 'CoinGate';
    case Binance = 'Binance';
    case LoadRateFake = 'LoadRateFake';

    public static function fromStore(Store $store): RateSource
    {
        if ($store->rate_source instanceof RateSource) {
            return $store->rate_source;
        }

        return RateSource::Binance;
    }

    public function getUri(): string
    {
        return match ($this)
        {
            RateSource::CoinGate => 'https://api.coingate.com/v2/rates',
            RateSource::Binance => 'https://api.binance.com/api/v3/ticker/price',
            RateSource::LoadRateFake => '',
        };
    }
}