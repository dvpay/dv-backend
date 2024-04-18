<?php

namespace App\Http\Resources\HotWallet;

use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Http\Resources\BaseResource;
use App\Models\Currency;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use Illuminate\Http\Request;

class HotWalletSummaryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'currencyId'       => $this->currency_id,
            'sumAmount'        => (float) $this->sum_amount,
            'sumAmountUsd'     => (float) $this->getSumAmountUsd(),
            'countWithBalance' => $this->count_with_balance,
            'createdCount'     => $this->created_count,
        ];
    }

    private function getSumAmountUsd(): string
    {
        $currencyConversion = app(CurrencyConversion::class);
        $currencyService = app(CurrencyRateService::class);

        $rateSource = RateSource::from(auth()->user()->rate_source);
        $currency = Currency::find($this->currency_id);
        $data = $currencyService->getCurrencyRate($rateSource, $currency->code, CurrencySymbol::USD);
        $amountUSD = $currencyConversion->convert($this->sum_amount, $data['rate'], true);
        return round($amountUSD,2,PHP_ROUND_HALF_DOWN);
    }
}

