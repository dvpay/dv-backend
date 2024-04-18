<?php

declare(strict_types=1);

namespace App\Services\RateSource;

use App\Enums\CurrencySymbol;
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

                if(
                    CurrencySymbol::BTC === $currency['from'] &&
                    in_array($currency['to'],[CurrencySymbol::USD,CurrencySymbol::USDT])
                ) {
                    $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '51224.1');
                    continue;
                }
                if(
                    CurrencySymbol::BTC === $currency['to'] &&
                    in_array($currency['from'],[CurrencySymbol::USD,CurrencySymbol::USDT])
                ) {
                    $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '0.00001952206');
                    continue;
                }

                if(
                    CurrencySymbol::ETH === $currency['from'] &&
                    in_array($currency['to'],[CurrencySymbol::USD,CurrencySymbol::USDT])
                ) {
                    $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '2952.12');
                    continue;
                }


                if(
                    CurrencySymbol::ETH === $currency['to'] &&
                    in_array($currency['from'],[CurrencySymbol::USD,CurrencySymbol::USDT])
                ) {
                    $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '0.00033873961');
                    continue;
                }

                if(
                    CurrencySymbol::TRX === $currency['from'] &&
                    in_array($currency['to'],[CurrencySymbol::USD,CurrencySymbol::USDT])
                ) {
                    $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '0.14');
                    continue;
                }

                if(
                    CurrencySymbol::TRX === $currency['to'] &&
                    in_array($currency['from'],[CurrencySymbol::USD,CurrencySymbol::USDT])
                ) {
                    $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '7.14285714286');
                    continue;
                }

                $this->currencyStore->set($rateSource, $currency['from'], $currency['to'], '1');
            }
        }
    }
}