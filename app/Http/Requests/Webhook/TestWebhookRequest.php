<?php

namespace App\Http\Requests\Webhook;

use App\Enums\WebhookType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TestWebhookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'eventType' => ['required', new Enum(WebhookType::class)],
            'orderId' => ['required', 'string', 'min:1', 'max:255', 'exists:invoices,order_id'],
        ];
    }
}
