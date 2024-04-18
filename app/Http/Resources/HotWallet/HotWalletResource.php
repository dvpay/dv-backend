<?php

namespace App\Http\Resources\HotWallet;

use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Http\Resources\BaseResource;
use App\Models\Currency;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use Illuminate\Http\Request;

/** @mixin \App\Models\HotWallet */
class HotWalletResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'currency'    => $this->currency_id,
            'currencyId'  => $this->currency_id,
            'address'     => $this->address,
            'blockchain'  => $this->blockchain,
            'createdAt'   => $this->created_at,
            'updatedTt'   => $this->updated_at,
            'balance'     => $this->amount,
            'balanceUsd'  => $this->getSumAmountUsd(),
            'explorerUrl' => $this->blockchain->getExplorerUrlAddress() . '/' . $this->address,
        ];
    }

    private function getSumAmountUsd(): string
    {
        $currencyConversion = app(CurrencyConversion::class);
        $currencyService = app(CurrencyRateService::class);

        $rateSource = RateSource::from(auth()->user()->rate_source);
        $currency = Currency::find($this->currency_id);
        $data = $currencyService->getCurrencyRate($rateSource, $currency->code, CurrencySymbol::USD);
        $amountUSD = $currencyConversion->convert($this->amount, $data['rate'], true);
        return round($amountUSD,2,PHP_ROUND_HALF_DOWN);
    }

}

