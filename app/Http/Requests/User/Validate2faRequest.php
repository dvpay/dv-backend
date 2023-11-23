<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class Validate2faRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'googleCode' => ['required', 'string', 'max:10']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}