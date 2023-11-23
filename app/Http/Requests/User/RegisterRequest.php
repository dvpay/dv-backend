<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email', 'unique:users', 'max:100'],
            'password' => ['required', 'confirmed', 'min:8', 'max:100', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[@$!%*#?&]/'],
            'name' => ['string', 'max:100', 'nullable'],
            'location' => ['string', 'nullable', 'timezone']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email is empty!',
            'email.email' => 'Email is invalid!',

            'password.required' => 'Password is empty!',
            'password.string' => 'Password must be string!',

            'name.required' => 'Name is empty!',
            'name.string' => 'Name must be string!',
        ];
    }
}
