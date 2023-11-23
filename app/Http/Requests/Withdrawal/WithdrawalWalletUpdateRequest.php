<?php

namespace App\Http\Requests\Withdrawal;

use App\Enums\WithdrawalInterval;
use App\Enums\WithdrawalRuleType;
use App\Rules\EnumKeyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class WithdrawalWalletUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'exchangeSlug'         => ['sometimes', 'string', 'nullable'],
            'addressType'          => ['required', 'string', new Enum(WithdrawalRuleType::class)],
            'withdrawalEnabled'    => ['required', 'boolean'],
            'withdrawalMinBalance' => ['required', 'integer'],
            'withdrawalInterval'   => ['required', 'string', new EnumKeyRule(WithdrawalInterval::class)],
            'address'              => ['required', 'array'],
            'address.*.'           => ['sometimes', 'string']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
