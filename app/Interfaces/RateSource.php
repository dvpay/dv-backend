<?php

namespace App\Interfaces;


interface RateSource
{
    public function loadCurrencyPairs(string $uri, array $currencies): void;
}