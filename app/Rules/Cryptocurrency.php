<?php

namespace App\Rules;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Models\Currency;
use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Cryptocurrency implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        $currency = Currency::where([
            ['id', $value],
            ['is_fiat', false],
        ])->exists();

        if (!$currency) {
            $fail('Is not cryptocurrency.');
        }
    }
}
