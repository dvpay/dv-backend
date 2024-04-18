<?php

namespace App\Http\Resources\Payer;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class PayerUndisclosesAddressResource extends BaseResource
{
    public function toArray(Request $request): array
    {

        $address = !$this->payer->store->status
            ? __('The store has suspended accepting payments, contact the owner to enable it')
            : $this->address;

        $address = !$this->payer->store->static_addresses
            ? __('Static address generation is disabled in store settings')
            : $address;

        return [
            'blockchain'              => $this->blockchain,
            'currency'                => $this->currency_id,
            'address'                 => $address,
            'rate'                    => $this->rate ?? null,
            'payer'                   => [
                'id'        => $this->payer->id,
                'store'     => $this->payer->store->id,
                'storeName' => $this->payer->store->name,
                'payerUrl'  => config('setting.payment_form_url') . '/payer/' . $this->payer->id,
            ],
            'transactions'            => $this->lastTransactions,
            'unconfirmedTransactions' => $this->lastUnconfirmedTransactions,
        ];
    }
}
