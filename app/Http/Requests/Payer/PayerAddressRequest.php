<?php

namespace App\Http\Requests\Payer;

use Illuminate\Foundation\Http\FormRequest;

class PayerAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
                'currency' => ['required', 'exists:currencies,id']
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
                'currency' => $this->route('currency'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
