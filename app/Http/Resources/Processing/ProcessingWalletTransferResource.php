<?php

namespace App\Http\Resources\Processing;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class ProcessingWalletTransferResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'txId'        => $this->txid,
            'type'        => $this->type,
            'fromAddress' => $this->from_address,
            'toAddress'   => $this->to_address,
            'amount'      => $this->amount,
            'amountUsd'   => $this->amount_usd,
            'energy'      => $this->energy,
            'bandwidth'   => $this->bandwidth,
            'createdAt'   => $this->created_at,
        ];
    }
}
