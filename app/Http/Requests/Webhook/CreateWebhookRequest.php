<?php

namespace App\Http\Requests\Webhook;

use App\Rules\WebhookTypes;
use Illuminate\Foundation\Http\FormRequest;

class CreateWebhookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'min:1', 'max:255'],
            'secret' => ['required', 'string', 'min:1', 'max:255'],
            'enabled' => ['required', 'boolean'],
            'events' => ['array', new WebhookTypes],
        ];
    }
}
