<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;

class IsValidToken implements DataAwareRule, ValidationRule
{
    protected string $country_code;
    protected ?string $number = null;

    public function __construct(
        protected string $user_type,
        protected string $country_code_input_name = 'country_code',
        protected string $number_input_name = 'number',
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->getNumber()) {
            return;
        }

        $mobile_number = MobileNumber::query()
            ->hasPhoneNumber($this->getCountryCode(), $this->getNumber(), $this->user_type)
            ->first();

        if (! $mobile_number) {
            $fail(trans('mobile-verification::strings.validation.token.invalid'));

            return;
        }

        if ($mobile_number->is_locked) {
            $fail(trans('mobile-verification::strings.validation.token.locked'));
        }

        if ($mobile_number->is_token_expired) {
            $fail(trans('mobile-verification::strings.validation.token.expired'));
        }

        if (! $mobile_number->verifyToken($value)) {
            $fail(trans('mobile-verification::strings.validation.token.invalid'));
        }
    }

    public function setCountryCode(?string $country_code): void
    {
        $this->country_code = $country_code ?: MobileVerification::defaultCountryCode();
    }

    public function getCountryCode(): string
    {
        return $this->country_code ?? MobileVerification::defaultCountryCode();
    }

    public function setNumber(?string $number): void
    {
        $this->number = $number;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setData(array $data): static
    {
        $this->setCountryCode($data[$this->country_code_input_name] ?? null);
        $this->setNumber($data[$this->number_input_name] ?? null);

        return $this;
    }
}
