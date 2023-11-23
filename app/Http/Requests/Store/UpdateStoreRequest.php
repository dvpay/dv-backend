<?php

namespace App\Http\Requests\Store;

use App\Rules\FiatCurrency;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
                'name'                  => ['required', 'string', 'min:1', 'max:255'],
                'site'                  => ['string', 'nullable', 'url', 'max:255'],
                'currency'              => ['required', 'string', 'max:255', new FiatCurrency],
                'invoiceExpirationTime' => ['required', 'int', 'min:1', 'max:1440'],
                'addressHoldTime'       => ['required', 'int', 'min:1', 'max:2880'],
                'status'                => ['required', 'boolean'],
                'staticAddresses'       => ['required', 'boolean'],
        ];
    }
}
