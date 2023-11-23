<?php

namespace App\Http\Resources\Exchange;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/** @mixin \App\Models\ExchangeWithdrawalWallet */
class ExchangeWithdrawalWalletResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'address'            => $this->address,
            'isWithdrawalEnable' => $this->is_withdrawal_enable,
            'minBalance'         => $this->min_balance,
            'chain'              => $this->chain,
            'currency'           => $this->currency,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
