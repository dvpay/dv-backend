<?php

declare(strict_types=1);

namespace App\ServiceLocator;

use App\Enums\RateSource;
use App\Interfaces\RateSource as RateSourceInterface;

class RateSourceLocator
{
    private readonly array $rateSources;

    public function __construct(
        RateSourceInterface ...$rateSources,
    )
    {
        $this->rateSources = $rateSources;
    }

    public function getRateSourceService(RateSource $rateSourceName): ?RateSourceInterface
    {
        foreach ($this->rateSources as $rateSource) {
            if (strpos($rateSource::class, $rateSourceName->value)) {
                return $rateSource;
            }
        }

        return null;
    }

}