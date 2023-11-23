<?php

namespace App\Http\Requests\Exchange;

use App\Enums\ExchangeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserPairsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'exchange' => ['required', new Enum(ExchangeService::class)],
            'directions' => ['sometimes', 'array'],
            'directions.*.currencyFrom' => ['required', 'string'],
            'directions.*.currencyTo' => ['required', 'string'],
            'directions.*.symbol' => ['required', 'string'],
            'directions.*.type' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
