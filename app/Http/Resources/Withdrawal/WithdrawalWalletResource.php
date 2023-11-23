<?php

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class WithdrawalWalletResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chain' => $this->chain,
            'blockchain' => $this->blockchain,
            'currency' => $this->currency,
            'type' => $this->type,
            'withdrawalEnabled' => $this->withdrawal_enabled,
            'withdrawalMinBalance' => $this->withdrawal_min_balance,
            'withdrawalInterval' => $this->withdrawal_interval,
            'address' => $this->address ? $this->address->pluck('address') : [],
            'exchange' => $this->exchange->slug ?? null
        ];
    }
}
