<?php

namespace App\Http\Resources\HotWallet;

use App\Http\Resources\BaseResource;
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
            'state'       => $this->state,
            'createdAt'   => $this->created_at,
            'updatedTt'   => $this->updated_at,
            'balance'     => $this->amount,
            'balanceUsd'  => $this->amount_usd,
            'explorerUrl' => $this->blockchain->getExplorerUrlAddress() . '/' . $this->address,
        ];
    }
}

