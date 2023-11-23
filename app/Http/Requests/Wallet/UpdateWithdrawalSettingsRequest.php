<?php

namespace App\Http\Requests\Wallet;

use App\Enums\ExchangeChainType;
use App\Enums\WithdrawalInterval;
use App\Rules\EnumKeyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateWithdrawalSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'address'                      => ['string', 'nullable'],
            'enabled'                      => ['bool', 'nullable'],
            'minBalance'                   => ['integer', 'nullable', 'min:0'],
            'enableAutomaticExchange'      => ['boolean', 'nullable'],
            'exchange'                     => ['string', 'nullable', 'exists:exchanges,slug'],
            'exchangeCurrencies'           => ['array', 'nullable'],
            'exchangeColdWalletIsEnabled'  => ['bool', 'nullable'],
            'exchangeColdWallet'           => ['array', 'nullable'],
            'exchangeColdWalletMinBalance' => ['numeric', 'nullable'],
            'exchangeChain'                => ['string', new Enum(ExchangeChainType::class)],
            'withdrawalIntervalCron'       => ['string', new EnumKeyRule(WithdrawalInterval::class)]
        ];
    }
}
