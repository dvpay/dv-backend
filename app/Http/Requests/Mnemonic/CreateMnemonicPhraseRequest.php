<?php

namespace App\Http\Requests\Mnemonic;

use Illuminate\Foundation\Http\FormRequest;

class CreateMnemonicPhraseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'passPhrase' => ['string', 'nullable'],
            'size' => ['integer', 'nullable'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'size.integer' => 'Size must be integer!',
        ];
    }
}
