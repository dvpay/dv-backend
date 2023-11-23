<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class RateStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'currency' => ['required', 'exists:currencies,id']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
