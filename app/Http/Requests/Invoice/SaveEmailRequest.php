<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class SaveEmailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => ['email', 'nullable', 'min:1', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email is empty!',
            'email.email' => 'Email is invalid!',
        ];
    }
}
