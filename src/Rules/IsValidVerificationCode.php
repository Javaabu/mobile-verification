<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;

class IsValidVerificationCode implements DataAwareRule, ValidationRule
{
    protected string $country_code;
    protected ?string $number = null;
    protected ?string $verification_code_id = null;
    protected bool $should_reset_attempts = false;

    public function __construct(
        protected string $user_type,
        protected string $country_code_input_name = 'country_code',
        protected string $number_input_name = 'number',
        protected string $verification_code_id_input_name = 'verification_code_id',
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
            $fail(trans('mobile-verification::strings.validation.verification_code.invalid'));

            return;
        }

        if (! ($this->getVerificationCodeId() && $this->getVerificationCodeId() == $mobile_number->verification_code_id)) {
            $fail(trans('mobile-verification::strings.validation.verification_code.invalid'));

            return;
        }

        if ($mobile_number->is_locked) {
            $fail(trans('mobile-verification::strings.validation.verification_code.locked'));
        }

        if ($mobile_number->is_verification_code_expired) {
            $fail(trans('mobile-verification::strings.validation.verification_code.expired'));
        }

        if (! $mobile_number->verifyVerificationCode($value, $this->should_reset_attempts)) {
            $fail(trans('mobile-verification::strings.validation.verification_code.invalid'));
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

    public function setVerificationCodeId(?string $verification_code_id): void
    {
        $this->verification_code_id = $verification_code_id;
    }

    public function getVerificationCodeId(): ?string
    {
        return $this->verification_code_id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function shouldResetAttempts(): static
    {
        return $this->setShouldResetAttempts(true);
    }

    public function setShouldResetAttempts(bool $reset): static
    {
        $this->should_reset_attempts = $reset;

        return $this;
    }

    public function setData(array $data): static
    {
        $this->setCountryCode($data[$this->country_code_input_name] ?? null);
        $this->setNumber($data[$this->number_input_name] ?? null);
        $this->setVerificationCodeId($data[$this->verification_code_id_input_name] ?? null);

        return $this;
    }
}
