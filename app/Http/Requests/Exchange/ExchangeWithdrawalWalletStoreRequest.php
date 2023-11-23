<?php

namespace App\Http\Requests\Exchange;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeWithdrawalWalletStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'address'              => ['required'],
            'is_withdrawal_enable' => ['required'],
            'min_balance'          => ['required'],
            'chain'                => ['required'],
            'currency'             => ['required'],
            'exchange'             => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
