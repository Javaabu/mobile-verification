<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;

class IsValidMobileNumber implements ValidationRule
{

    public function __construct(
        public string | null $user_type = null,
        public string | null $country_code = null,
    )
    {
        $this->country_code ??= Countries::Maldives->getCountryCode();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mobile_number = MobileNumber::query()->hasPhoneNumberWithOwner($this->country_code, $value, $this->user_type)->exists();
        if ($mobile_number) {
            $fail(trans('mobile-verification::strings.validation.number.exists', ['attribute' => $attribute]));
        }

        if ($this->country_code != '960') {
            return;
        }

        if (!in_array(substr($value, 0, 1), ['7', '9'])) {
            $fail(trans('mobile-verification::strings.validation.number.invalid', ['attribute' => $attribute]));
        }

        // fail if the number is not 7 digits long
        if (strlen($value) !== 7) {
            $fail(trans('mobile-verification::strings.validation.number.invalid_length', ['attribute' => $attribute]));
        }
    }
}
