<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\MobileVerification;

/**
 * One of `mobile-verification.allowed_country_codes`, with or without the prefix
 */
class IsValidCountryCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_scalar($value) || ! in_array(MobileVerification::normalizeCountryCode((string) $value, false), MobileVerification::allowedCountryCodes(), true)) {
            $fail(__('The country code is not allowed.'));
        }
    }
}
