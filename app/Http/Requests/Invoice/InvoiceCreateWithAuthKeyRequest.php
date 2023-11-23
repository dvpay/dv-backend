<?php

namespace App\Http\Requests\Invoice;

use App\Rules\FiatCurrency;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceCreateWithAuthKeyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'storeId' => ['required', 'string', 'min:1', 'max:255', 'exists:stores,id'],
            'orderId' => ['required', 'string', 'min:1', 'max:255'],
            'amount' => ['numeric', 'nullable'],
            'currency' => ['string', 'nullable', new FiatCurrency],
            'description' => ['string', 'nullable', 'min:1', 'max:255'],
            'returnUrl' => ['string', 'nullable', 'min:1', 'max:255'],
            'successUrl' => ['string', 'nullable', 'min:1', 'max:255'],
            'paymentMethod' => ['string', 'min:1', 'max:255', 'exists:currencies,id'],
            'custom' => ['array', 'nullable'],
        ];
    }
}
