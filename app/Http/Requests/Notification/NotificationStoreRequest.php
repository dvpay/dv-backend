<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'notifications' => ['sometimes','array', "min:0"],
            'notifications.*' => ['required', 'integer', 'exists:notifications,id']

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}