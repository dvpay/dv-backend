<?php

namespace App\Http\Requests\Exchange;

use App\Enums\ExchangeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ExchangeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'exchange'  => ['required', new Enum(ExchangeService::class)],
            'symbols' => ['sometimes', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
