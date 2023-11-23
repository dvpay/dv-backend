<?php

declare(strict_types=1);

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * InvoiceAddressesListRequest
 */
class InvoiceAddressesListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'page' => ['integer', 'nullable'],
            'perPage' => ['integer', 'nullable'],
            'sortField' => Rule::in(['created_at', 'updated_at', 'state', 'balance', 'balance_usd']),
            'sortDirection' => Rule::in(['desc', 'asc']),
            'filterField' => ['nullable', Rule::in(['state', 'currency_id'])],
            'filterValue' => ['nullable', 'string'],
            'hideEmpty' => ['nullable', 'boolean'],
        ];
    }
}
