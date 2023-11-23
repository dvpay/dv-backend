<?php

declare(strict_types=1);

namespace App\Http\Resources\WalletBalance;

use App\Enums\CurrencySymbol;
use App\Http\Resources\BaseResource;
use App\Models\Currency;
use App\Models\WalletBalance;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\BalanceGetter;
use Illuminate\Http\Request;
use OpenApi\Attributes\Get;

/**
 * @property WalletBalance $resource
 */
class WalletBalanceResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'currencyId' => $this->currency_id,
            'balance' => $this->resource->balance,
            'balanceUsd' => $this->getBalanceUsd()
        ];
    }

    private function getBalanceUsd(): string
    {
        $currencyConversion = app(CurrencyConversion::class);
        $currencyService = app(CurrencyRateService::class);

        $rateSource = $this->wallet->user->rate_source;
        $currency = Currency::find($this->currency_id);
        $data = $currencyService->getCurrencyRate($rateSource, $currency->code, CurrencySymbol::USDT);

        return $currencyConversion->convert($this->resource->balance, $data['rate'], true);
    }
}