<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Javaabu\MobileVerification\MobileVerification;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;

class IsValidCountryCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = MobileVerification::normalizeNumber($value);
        $allowed_country_codes = config('mobile-verification.allowed_country_codes');
        if (! in_array($value, $allowed_country_codes)) {
            $fail(trans('mobile-verification::strings.validation.country_code.invalid'));
        }
    }
}
