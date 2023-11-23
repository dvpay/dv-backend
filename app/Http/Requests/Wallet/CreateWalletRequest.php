<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class CreateWalletRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'remember' => ['required', 'accepted'],
            'readonly' => ['required', 'boolean'],
            'mnemonic' => ['required', 'string'],
            'passPhrase' => ['string', 'nullable', 'confirmed'],
        ];
    }
}
