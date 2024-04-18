<?php

namespace App\Http\Requests\Withdrawal;

use App\Enums\WithdrawalInterval;
use App\Rules\EnumKeyRule;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalWalletUpdateWithdrawalRulesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'withdrawalMinBalance' => ['required', 'integer'],
            'withdrawalInterval'   => ['required', 'string', new EnumKeyRule(WithdrawalInterval::class)],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
