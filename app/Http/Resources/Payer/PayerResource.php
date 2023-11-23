<?php

namespace App\Http\Resources\Payer;

use App\Enums\CurrencyId;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class PayerResource extends BaseResource
{
    protected array $rate;

    public function setRate($value)
    {
        $this->rate = $value;
        return $this;
    }
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'store_id'      => $this->store_id,
            'store_user_id' => $this->store_user_id,
            'store'         => [
                'id'                => $this->store->id,
                'name'              => $this->store->name,
                'status'            => $this->store->status,
                'staticAddress'     => $this->store->static_addresses,
                'storeCurrencyCode' => $this->store->currency->code,
            ],
            'address'       => PayerAddressCollection::make($this->payerAddresses),
            'currency'      => CurrencyId::enabledCurrency(),
            'rate'          => $this->rate,
            'payerUrl'      => config('setting.payment_form_url') . '/payer/' . $this->id,
        ];
    }
}
