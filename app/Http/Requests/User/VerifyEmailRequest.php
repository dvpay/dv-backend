<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'   => ['required', 'integer'],
            'hash' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}