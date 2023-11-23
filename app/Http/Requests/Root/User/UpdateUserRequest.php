<?php

namespace App\Http\Requests\Root\User;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'roles' => ['required','array'],
            'roles.*' => ['required', 'string', 'in:' . implode(',',UserRole::values())]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}