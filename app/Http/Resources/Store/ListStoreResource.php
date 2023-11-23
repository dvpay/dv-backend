<?php

declare(strict_types=1);

namespace App\Http\Resources\Store;

use App\Http\Resources\BaseResource;
use Exception;
use Illuminate\Http\Request;

class ListStoreResource extends BaseResource
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
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'createdAt'      => $this->created_at->format(DATE_ATOM),
            'invoicesCount'  => $this->invoices_success_count,
            'invoicesAmount' => $this->invoices_success_sum_amount
        ];
    }
}