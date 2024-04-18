<?php

namespace App\Rules;

use App\Enums\Blockchain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TronAddressRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $regex = Blockchain::Tron->getAddressValidationRegex();

        if (!preg_match($regex, $value)) {
            $fail("The {$value} must be a valid TRC-20 address.");
        }
    }
}
