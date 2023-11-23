<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUrlsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'returnUrl' => ['required', 'string', 'nullable', 'min:3', 'max:255'],
            'successUrl' => ['required', 'string', 'nullable', 'min:3', 'max:255'],
        ];
    }
}
