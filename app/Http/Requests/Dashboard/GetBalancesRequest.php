<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\TimeRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
            'currencyId' => ['required', 'string', 'min:1', 'max:255', 'exists:currencies,id'],
            'range' => ['required', 'string', new Enum(TimeRange::class)],
        ];
    }
}
