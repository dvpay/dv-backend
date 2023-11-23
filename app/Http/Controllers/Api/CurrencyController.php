<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\DefaultResponseResource;
use App\Services\Currency\CurrencyRateService;

/**
 * CurrencyController
 */
class CurrencyController extends ApiController
{
    /**
     * @param CurrencyRateService $currencyRateService
     */
    public function __construct(private readonly CurrencyRateService $currencyRateService)
    {
    }

    /**
     * Get the exchange rate for all currency pairs
     *
     * @return DefaultResponseResource
     */
    public function getAllRates(): DefaultResponseResource
    {
        $result = $this->currencyRateService->getAllRates();

        return (new DefaultResponseResource($result));
    }
}