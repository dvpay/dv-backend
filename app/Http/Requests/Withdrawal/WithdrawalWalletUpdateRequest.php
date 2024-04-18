<?php

namespace App\Http\Requests\Withdrawal;

use App\Enums\Blockchain;
use App\Enums\WithdrawalInterval;
use App\Enums\WithdrawalRuleType;
use App\Rules\BitcoinAddressRule;
use App\Rules\EnumKeyRule;
use App\Rules\TronAddressRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class WithdrawalWalletUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rule = match (($this->route('withdrawalWallet'))->blockchain) {
            Blockchain::Bitcoin => new BitcoinAddressRule,
            Blockchain::Tron => new TronAddressRule,
        };
        return [
            'exchangeSlug'         => ['sometimes', 'string', 'nullable'],
            'addressType'          => ['sometimes', 'string', new Enum(WithdrawalRuleType::class)],
            'withdrawalEnabled'    => ['sometimes', 'boolean', 'nullable'],
            'withdrawalMinBalance' => ['sometimes', 'integer', 'nullable'],
            'withdrawalInterval'   => ['sometimes', 'string', 'nullable', new EnumKeyRule(WithdrawalInterval::class)],
            'address'              => ['sometimes', 'array'],
            'address.*'            => ['sometimes', $rule],
            'validateCode'         => ['sometimes', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
