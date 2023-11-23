<?php

namespace App\Helpers;

use App\Enums\CurrencyId;

class CommissionCalculation
{
    public static function savedOnCommission(string $currencyId, float $incomingTransactions, float $outcomingTransactions): float|int
    {
        $commission = match ($currencyId) {  // USD
            CurrencyId::UsdtTron->value => 0.62,
            CurrencyId::BtcBitcoin->value => 0.8,
            default => 0.5
        };

        (int)$savedTransactions = $incomingTransactions - $outcomingTransactions;

        if ($savedTransactions > 0) {
            return round($savedTransactions * $commission, 2);
        }
        return 0;
    }
}