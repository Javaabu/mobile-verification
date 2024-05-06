<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsValidMobileNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array(substr($value, 0, 1), ['7', '9'])) {
            $fail(trans('mobile-verification::strings.validation.number.invalid', ['attribute' => $attribute]));
        }
    }
}
