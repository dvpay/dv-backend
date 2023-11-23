<?php

declare(strict_types=1);

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * @property Transaction $resource
 */
class WithdrawalListResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'txId' => $this->resource->tx_id,
            'currency' => $this->resource->currency_id,
            'createdAt' => $this->resource->created_at,
            'amount' => $this->resource->amount,
            'address' => $this->resource->to_address,
            'isManual' => $this->resource->withdrawal_is_manual,
        ];
    }
}