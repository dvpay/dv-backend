<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use DateTime;
use Illuminate\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * CurrencyStore
 */
class CurrencyStore
{
    /**
     * @param Repository $cache
     * @param int $cacheLifetime
     */
    public function __construct(
        private readonly Repository $cache,
        private readonly int $cacheLifetime
    )
    {
    }

    /**
     * @param RateSource $rateSource
     * @param CurrencySymbol $from
     * @param CurrencySymbol $to
     * @param string $rate
     * @return bool
     * @throws InvalidArgumentException
     */
    public function set(RateSource $rateSource, CurrencySymbol $from, CurrencySymbol $to, string $rate): bool
    {
        $key = $this->generateKey($rateSource->value, $from->value, $to->value);

        $data = [
            'rate' => $rate,
            'lastUpdate' => new DateTime(),
        ];

        return $this->cache->set($key, $data, $this->cacheLifetime);
    }

    /**
     * @param RateSource $rateSource
     * @param CurrencySymbol $from
     * @param CurrencySymbol $to
     * @return array|null
     */
    public function get(RateSource $rateSource, CurrencySymbol $from, CurrencySymbol $to): ?array
    {
        $key = $this->generateKey($rateSource->value, $from->value, $to->value);

        if ($result = $this->cache->get($key)) {
            return $result;
        }

        return null;
    }

    /**
     * @param string $rateSource
     * @param string $from
     * @param string $to
     * @return string
     */
    private function generateKey(string $rateSource, string $from, string $to): string
    {
        return "$rateSource:$from:$to";
    }
}
