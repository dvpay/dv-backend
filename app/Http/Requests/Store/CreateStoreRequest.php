<?php

namespace App\Http\Requests\Store;

use App\Rules\FiatCurrency;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'currency' => ['string', 'min:1', 'max:255', new FiatCurrency],
            'rateSource' => ['string', 'min:1', 'max:255', 'exists:rate_sources,name'],
        ];
    }
}
