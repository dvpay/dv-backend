<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetListInvoicesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'query' => ['string', 'nullable', 'min:1', 'max:255'],
            'page' => ['integer', 'nullable'],
            'perPage' => ['integer', 'nullable'],
            'sortField' => Rule::in(['created_at', 'updated_at', 'amount']),
            'sortDirection' => Rule::in(['desc', 'asc']),
            'stores' => ['array', 'nullable'],
        ];
    }
}
