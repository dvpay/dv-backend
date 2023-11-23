<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangeGoogle2faRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status'     => ['required', 'boolean'],
            'googleCode' => ['sometimes', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}