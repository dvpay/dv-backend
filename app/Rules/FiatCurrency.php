<?php

namespace App\Rules;

use App\Models\Currency;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FiatCurrency implements ValidationRule
{
    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currency = Currency::where([
                ['code', $value],
                ['is_fiat', true],
        ])->exists();

        if (!$currency) {
            $fail('Is not fiat currency!');
        }
    }
}
