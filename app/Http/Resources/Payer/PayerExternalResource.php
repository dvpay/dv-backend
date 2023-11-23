<?php

namespace App\Http\Resources\Payer;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class PayerExternalResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'store_id'      => $this->store_id,
            'store_user_id' => $this->store_user_id,
            'payerUrl'      => config('setting.payment_form_url') . '/payer/' . $this->id,
            'store'         => [
                'id'                => $this->store->id,
                'name'              => $this->store->name,
                'status'            => $this->store->status,
                'staticAddress'     => $this->store->static_addresses,
                'storeCurrencyCode' => $this->store->currency->code,
            ],
            'address'       => PayerAddressCollection::make($this->payerAddresses),
        ];
    }
}
