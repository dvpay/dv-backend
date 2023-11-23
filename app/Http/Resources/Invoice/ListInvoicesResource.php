<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;
use Exception;

class ListInvoicesResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'orderId' => $this->order_id,
            'createdAt' => $this->created_at,
            'status' => $this->status->value,
            'amount' => $this->amount,
            'currency' => $this->currency->code,
            'storeName' => $this->name,
            'paymentUrl' => config('setting.payment_form_url') . '/' . $this->id,
        ];
    }
}
