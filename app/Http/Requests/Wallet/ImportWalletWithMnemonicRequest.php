<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class ImportWalletWithMnemonicRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'mnemonic' => ['required', 'string'],
            'passPhrase' => ['string', 'nullable', 'confirmed'],
        ];
    }
}
