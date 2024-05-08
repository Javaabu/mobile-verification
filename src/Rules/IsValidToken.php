<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;

class IsValidToken implements ValidationRule
{
    public function __construct(
        public string | null $user_type = null,
        public string | null $number = null,
        public string | null $country_code = null,
    ) {
        $this->country_code ??= Countries::Maldives->getCountryCode();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->number) {
            return;
        }

        $mobile_number = MobileNumber::query()
            ->hasPhoneNumber($this->country_code, $this->number, $this->user_type)
            ->first();

        if (! $mobile_number) {
            $fail(trans('mobile-verification::strings.validation.token.invalid'));

            return;
        }

        if ($mobile_number->is_token_expired) {
            $fail(trans('mobile-verification::strings.validation.token.expired'));
        }

        if (! $mobile_number->verifyToken($value)) {
            $fail(trans('mobile-verification::strings.validation.token.invalid'));
        }
    }
}
