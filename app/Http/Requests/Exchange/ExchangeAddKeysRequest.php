<?php

namespace App\Http\Requests\Exchange;

use App\Enums\ExchangeService;
use App\Rules\EnumKeyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ExchangeAddKeysRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'exchange'  => ['required', new EnumKeyRule(ExchangeService::class)],
            'keys' => ['required', 'array'],
            'keys.*.name' => ['required', 'string'],
            'keys.*.value' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
