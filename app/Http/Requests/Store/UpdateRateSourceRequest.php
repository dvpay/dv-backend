<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRateSourceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'rateSource' => ['required', 'string', 'min:1', 'max:255', 'exists:rate_sources,name'],
            'rateScale' => ['required', 'numeric', 'between:0,3']
        ];
    }
}
