<?php

namespace App\Http\Requests\Withdrawal;

use App\Enums\WithdrawalInterval;
use App\Enums\WithdrawalRuleType;
use App\Rules\EnumKeyRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class WithdrawalIntervalRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'withdrawalMinBalance' => ['required', 'integer'],
            'withdrawalIntervalCron' => ['required', 'string', new EnumKeyRule(WithdrawalInterval::class)],
            'withdrawalRuleType' => ['required', 'string', new Enum(WithdrawalRuleType::class)],
        ];
    }

}
