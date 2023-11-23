<?php

namespace App\Http\Requests\Payer;

use Illuminate\Foundation\Http\FormRequest;

class PayerStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
                'storeId'     => ['required', 'string', 'min:1', 'max:255', 'exists:stores,id'],
                'storeUserId' => ['required', 'regex:/^[a-z0-9\-]+$/'],
        ];
    }

    public function messages()
    {
        return [
                'storeUserId.regex' => 'The payer can only contain uppercase and lowercase letters, a number and a symbol \'-\''
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
