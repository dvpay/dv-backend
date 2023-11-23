<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'    => ['sometimes', 'bail', 'email', 'unique:users', 'max:100'],
            'location' => ['sometimes', 'string'],
            'language' => ['sometimes', 'string'],
            'phone'    => ['sometimes', 'string', 'min:8'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}