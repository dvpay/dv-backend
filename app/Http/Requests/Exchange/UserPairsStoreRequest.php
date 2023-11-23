<?php

namespace App\Http\Requests\Exchange;

use App\Enums\ExchangeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserPairsStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'exchange'   => ['required', new Enum(ExchangeService::class)],
            'currencyFrom' => ['required', 'string'],
            'currencyTo'   => ['required', 'string'],
            'symbol'        => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
