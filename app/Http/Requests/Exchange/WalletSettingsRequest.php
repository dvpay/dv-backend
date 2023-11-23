<?php

namespace App\Http\Requests\Exchange;

use App\Enums\ExchangeService;
use App\Enums\WithdrawalInterval;
use App\Rules\EnumKeyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class WalletSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'withdrawalEnabled' => ['required', 'boolean'],
            'address' => ['nullable'],
            'withdrawalMinBalance' => ['sometimes', 'nullable', 'numeric'],
            'enableAutomaticExchange' => ['required', 'boolean'],
            'exchange' => ['required', new Enum(ExchangeService::class)],
            'exchangeCurrenciesFrom' => ['sometimes', 'nullable', 'string'],
            'exchangeCurrenciesTo' => ['sometimes', 'nullable', 'string'],
            'exchangeColdWalletAddress' => ['sometimes', 'nullable', 'array'],
            'exchangeColdWalletAddress.*.address' => ['required', 'string'],
            'exchangeColdWalletAddress.*.chain' => ['required', 'string'],
            'exchangeColdWalletAddress.*.isWithdrawalEnabled' => ['boolean'],
            'exchangeColdWalletAddress.*.withdrawalMinBalance' => ['numeric'],
            'withdrawalIntervalCron' => ['string', 'sometimes',  new EnumKeyRule(WithdrawalInterval::class)]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
