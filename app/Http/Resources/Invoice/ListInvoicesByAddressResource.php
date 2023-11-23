<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;
use App\Models\InvoiceStatusHistory;
use Exception;
use Illuminate\Http\Request;

class ListInvoicesByAddressResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        $statuses = InvoiceStatusHistory::where('invoice_id', $this->id)
            ->orderBy('created_at')
            ->get();

        $invoiceStatuses = [];
        foreach ($statuses as $status) {
            $invoiceStatuses[] = [
                'status' => $status->status,
                'datetime' => $status->created_at,
            ];
        }

        return [
            'id' => $this->id,
            'orderId' => $this->order_id,
            'createdAt' => $this->created_at,
            'statuses' => $invoiceStatuses,
            'amount' => $this->amount,
            'currency' => $this->currency->id,
            'storeName' => $this->name,
        ];
    }
}
