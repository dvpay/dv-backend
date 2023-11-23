<?php

declare(strict_types=1);

namespace App\Services\RateSource;

use App\Enums\RateSource;
use App\Interfaces\RateSource as RateSourceInterface;
use App\Services\Currency\CurrencyStore;
use Psr\SimpleCache\InvalidArgumentException;

class LoadRateFake implements RateSourceInterface
{
    public function __construct(
        private readonly CurrencyStore $currencyStore
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function loadCurrencyPairs(string $uri, array $currencies): void
    {
        foreach ($currencies as $currency) {
            $rateSources = RateSource::cases();
            foreach ($rateSources as $rateSource) {
                $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '1');
            }
        }
    }
}