<?php

namespace App\Http\Resources\Wallet;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class ColdWalletResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            "walletId"             => $this->wallet_id,
            "address"              => $this->address,
            "isWithdrawalEnabled"  => $this->is_withdrawal_enabled,
            "withdrawalMinBalance" => $this->withdrawal_min_balance,
            "chain"                => $this->chain,
        ];
    }
}
