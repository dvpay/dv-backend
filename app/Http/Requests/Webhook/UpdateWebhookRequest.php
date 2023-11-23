<?php

namespace App\Http\Requests\Webhook;

use App\Rules\WebhookTypes;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'min:1', 'max:255'],
            'secret' => ['required', 'string', 'min:1', 'max:255'],
            'enabled' => ['required', 'boolean'],
            'events' => ['array', new WebhookTypes],
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
            'url.required' => 'URL is empty!',
            'url.string' => 'URL must be string!',

            'secret.required' => 'Secret is empty!',
            'secret.string' => 'Secret must be string!',

            'enabled.required' => 'Enabled is empty!',
            'enabled.string' => 'Enabled must be boolean!',

            'events.array' => 'Events must be array!',
        ];
    }
}
