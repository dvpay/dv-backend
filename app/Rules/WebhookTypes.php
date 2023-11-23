<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\WebhookType as Events;

class WebhookTypes implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail): void
    {
        if ($value == []) {
            return;
        }

        foreach ($value as $event) {
            if (Events::tryFrom($event) === null) {
                $fail('The :attribute is invalid!');
            }
        }
    }
}
