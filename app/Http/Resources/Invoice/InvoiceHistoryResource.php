<?php

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class InvoiceHistoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $variables = is_array($this->text_variables)
            ? array_map(fn($v) => is_array($v) ? implode('', $v) : $v, $this->text_variables)
            : [];
        return [
            'createdAt' => $this->created_at,
            'text'      => __($this->text, $variables),
        ];
    }
}
