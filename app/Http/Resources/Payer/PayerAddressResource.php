<?php

namespace App\Http\Resources\Payer;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class PayerAddressResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'blockchain'   => $this->blockchain,
            'currency'     => $this->currency_id,
            'address'      => $this->address,
            'rate'         => $this->rate ?? null,
            'payer'        => [
                'id'          => $this->payer->id,
                'storeUserId' => $this->payer->store_user_id,
                'store'       => $this->payer->store->id,
                'storeName'   => $this->payer->store->name,
                'payerUrl'    => config('setting.payment_form_url') . '/payer/' . $this->payer->id,
            ],
            'transactions' => $this->transactions,
        ];
    }
}
