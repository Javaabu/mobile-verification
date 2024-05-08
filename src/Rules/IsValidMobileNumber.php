<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;

class IsValidMobileNumber implements ValidationRule
{
    public function __construct(
        public string $user_type,
        public string|null $country_code = null,
        public bool|null   $can_be_taken_by_user = null,
        public bool $can_send_otp = false,
    )
    {
        $this->country_code ??= Countries::Maldives->getCountryCode();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mobile_number = MobileNumber::query()
                                     ->hasPhoneNumber($this->country_code, $value, $this->user_type)
                                     ->first();

        if ($mobile_number && ! $this->can_be_taken_by_user && filled($mobile_number->user_id)) {
            $fail(trans('mobile-verification::strings.validation.number.exists', ['attribute' => $attribute]));
        }

        if ($mobile_number && $this->can_send_otp && $mobile_number->is_locked) {
            $fail(trans('mobile-verification::strings.validation.number.locked'));
        }

        if ($this->country_code != '960') { // Check below validation only for Maldives
            return;
        }

        if (! in_array(substr($value, 0, 1), ['7', '9'])) { // Check format
            $fail(trans('mobile-verification::strings.validation.number.invalid', ['attribute' => $attribute]));
        }

        // fail if the number is not 7 digits long
        if (strlen($value) !== 7) { // Check length
            $fail(trans('mobile-verification::strings.validation.number.invalid_length', ['attribute' => $attribute]));
        }
    }
}
