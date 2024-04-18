<?php

namespace App\Http\Requests\Withdrawal;

use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Rules\BitcoinAddressRule;
use App\Rules\TronAddressRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class WithdrawalWalletWithdrawFromProcessingRequest extends FormRequest
{
    public function rules(): array
    {
        $rule = match (CurrencyId::tryFrom($this->currencyId)?->getBlockchain()) {
            Blockchain::Bitcoin->value => new BitcoinAddressRule,
            Blockchain::Tron->value => new TronAddressRule,
        };

        return [
            'currencyId' => ['required', new Enum(CurrencyId::class)],
            'addressTo'  => ['required', $rule],
            'amount'     => ['required', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
