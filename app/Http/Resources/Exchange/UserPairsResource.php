<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class UserPairsResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'currencyFrom' => $this->currency_from,
            'currencyTo' => $this->currency_to,
            'symbol' => $this->symbol,
            'type' => $this->type,
            'label' => $this->type === 'sell' ? $this->currency_from . '/' . $this->currency_to : $this->currency_to . '/' . $this->currency_from,
        ];
    }
}
