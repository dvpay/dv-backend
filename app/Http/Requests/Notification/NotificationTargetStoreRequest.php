<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationTargetStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
                'targets'   => ['sometimes', 'array', "min:0"],
                'targets.*' => ['required', 'integer', 'exists:notification_targets,id']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
