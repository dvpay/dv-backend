<?php

namespace App\Http\Resources\HotWallet;

use App\Helpers\CommissionCalculation;
use App\Http\Resources\BaseResource;

/** @mixin \App\Models\UserInvoiceAddress */
class HotWalletDetailResource extends BaseResource
{
    public function toArray($request)
    {
        return [
            'address'              => $this->address,
            'state'                => $this->state,
            'invoices'             => [
                'total' => $this->invoices_count,
                'paid'  => $this->paid_invoices_count,
            ],
            'transactions'         => [
                'incoming' => [
                    'count'     => $this->transactions_incoming_count,
                    'amountUsd' => (float)$this->transactions_incoming_sum_amount_usd,
                ],
                'outgoing' => [
                    'count'     => $this->transactions_outgoing_count,
                    'amountUsd' => (float)$this->transactions_outgoing_sum_amount_usd,
                ]
            ],
            'savedUsdOnCommission' => CommissionCalculation::savedOnCommission(
                currencyId: $this->currency_id,
                incomingTransactions: (float)$this->transactions_incoming_sum_amount_usd,
                outcomingTransactions: (float)$this->transactions_outgoing_sum_amount_usd
            )
        ];
    }
}
