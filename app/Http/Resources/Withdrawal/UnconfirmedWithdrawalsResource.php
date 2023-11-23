<?php

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseResource;

/**
 * @property []Transactions $resource
 */
class UnconfirmedWithdrawalsResource extends BaseResource
{
    public function toArray($request)
    {
        $total = array_column($this->resource, 'amount');
        $totalUsd = array_column($this->resource, 'amount_usd');

        return [
            'total' => array_sum($total),
            'totalUsd' => array_sum($totalUsd),
            'transactions' => array_column($this->resource, 'tx_id')
        ];
    }
}