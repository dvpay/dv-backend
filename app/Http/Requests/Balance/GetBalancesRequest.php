<?php

namespace App\Http\Requests\Balance;

use App\Rules\FiatCurrency;
use Illuminate\Foundation\Http\FormRequest;

class GetBalancesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'stores' => ['array', 'nullable'],
        ];
    }
}
