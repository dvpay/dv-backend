<?php

namespace App\Http\Requests\Invite;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserInviteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'roles'    => ['array', 'nullable'],
            'roles.*'  => ['distinct', 'string', 'in:' . implode(',', UserRole::values()), 'not_in:' . UserRole::Root->value],
            'stores'   => ['array', 'nullable'],
            'stores.*' => ['string'],

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}