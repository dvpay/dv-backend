<?php

namespace App\Http\Requests\Payer;

use Illuminate\Foundation\Http\FormRequest;

class PayerAddressShowRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payer'    => ['required', 'regex:/^[a-z0-9\-]+$/'],
            'currency' => ['required', 'exists:currencies,id'],
            'email'    => ['sometimes', 'email'],
            'ip'       => ['sometimes', 'ip', 'nullable'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payer'    => $this->route('payer'),
            'currency' => $this->route('currency'),
        ]);
    }

    public function messages()
    {
        return [
            'payer.regex' => 'The payer can only contain uppercase and lowercase letters, a number and a symbol \'-\'',
            'currency'    => 'Currency not found'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
