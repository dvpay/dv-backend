<?php

declare(strict_types=1);

namespace App\Http\Resources\Store;

use App\Http\Resources\BaseResource;

class StoreResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name ?? '',
            'site'                  => $this->site ?? '',
            'currency'              => $this->currency->code->value,
            'rateSource'            => $this->rate_source->value,
            'invoiceExpirationTime' => $this->invoice_expiration_time,
            'addressHoldTime'       => $this->address_hold_time,
            'returnUrl'             => $this->return_url,
            'successUrl'            => $this->success_url,
            'status'                => $this->status,
            'staticAddresses'       => $this->static_addresses
        ];
    }
}
