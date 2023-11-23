<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class GetCurrencyRateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'from' => ['required', 'string', 'min:1', 'max:255', 'exists:currencies,code'],
            'to' => ['required', 'string', 'min:1', 'max:255', 'exists:currencies,code'],
            'amount' => ['required', 'string'],
        ];
    }
}
