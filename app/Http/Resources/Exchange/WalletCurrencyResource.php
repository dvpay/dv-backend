<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class WalletCurrencyResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'fromCurrencyId' => $this->from_currency_id,
            'toCurrencyId'   => $this->to_currency_id,
        ];
    }
}
