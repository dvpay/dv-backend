<?php

declare(strict_types=1);

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

/**
 * @property Transaction $resource
 */
class WithdrawalStatsResource extends BaseResource
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
            'date' => $this->resource->date,
            'amountUsd' => $this->resource->amountUsd,
            'transactionCount' => $this->resource->transactionCount,
        ];
    }
}
