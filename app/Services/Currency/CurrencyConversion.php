<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Services\Currency\Interfaces\CurrencyConversionInterface;
/**
 * CurrencyConversion
 */
class CurrencyConversion implements CurrencyConversionInterface
{
    /**
     * @param string $amount
     * @param string $rate
     * @param bool $reverseRate
     * @return string
     */
    public function convert(string $amount, string $rate, bool $reverseRate = false): string
    {
        if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $amount)) {
            // Convert $amount from exponential notation to a decimal string
            $amount = sprintf("%.10f", $amount);
        }
        if ($reverseRate) {
            $rate = bcdiv("1", $rate);
        }

        return bcmul($amount, $rate);
    }
}
