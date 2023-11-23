<?php

namespace App\Http\Requests\Invite;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class SendInvateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string', 'unique:users,email'],
            'role'  => ['required', 'not_in:' . UserRole::Admin->value . ',' . UserRole::Root->value]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}