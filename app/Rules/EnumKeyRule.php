<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnumKeyRule implements ValidationRule
{
    protected $enumClass;

    public function __construct($enumClass)
    {
        $this->enumClass = $enumClass;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!in_array($value, array_keys($this->enumClass::array()))) {
            $fail('The :attribute is invalid!');
        };

    }
}
