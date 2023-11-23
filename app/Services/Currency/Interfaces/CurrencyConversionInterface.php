<?php

namespace App\Services\Currency\Interfaces;

interface CurrencyConversionInterface
{
    public function convert(string $amount, string $rate, bool $reverseRate): string;
}