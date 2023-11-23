<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\TimeRange;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class GetDepositTransactionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
	        'range' => ['required', 'string', new Enum(TimeRange::class)],
            'stores' => ['array', 'nullable'],
        ];
    }
}
