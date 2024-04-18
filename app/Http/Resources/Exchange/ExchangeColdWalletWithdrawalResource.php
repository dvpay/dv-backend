<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class ExchangeColdWalletWithdrawalResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'address'   => $this->address,
            'amount'    => $this->amount,
            'createdAt' => $this->created_at,
        ];
    }
}
