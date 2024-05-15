<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Javaabu\MobileVerification\MobileVerification;
use Illuminate\Contracts\Validation\ValidationRule;
use Javaabu\MobileVerification\Contracts\IsANumberFormatValidator;

class IsValidMobileNumber implements DataAwareRule, ValidationRule
{
    protected bool|null $should_be_registered_number = null;
    protected bool $can_send_otp = false;
    protected string $country_code;
    protected string $ignore_user_id;

    public function __construct(
        protected string $user_type,
        protected string $country_code_input_name = 'country_code',
    ) {
    }

    public function registered(): static
    {
        return $this->setShouldBeRegisteredNumber(true);
    }

    public function notRegistered(): static
    {
        return $this->setShouldBeRegisteredNumber(false);
    }

    public function setShouldBeRegisteredNumber(?bool $value = null): static
    {
        $this->should_be_registered_number = $value;
        return $this;
    }

    public function canSendOtp(): static
    {
        $this->can_send_otp = true;
        return $this;
    }

    public function setCountryCode(?string $country_code): void
    {
        $this->country_code = $country_code ?: MobileVerification::defaultCountryCode();
    }

    public function getCountryCode(): string
    {
        return $this->country_code ?? MobileVerification::defaultCountryCode();
    }

    public function ignore(string $user_id): static
    {
        $this->ignore_user_id = $user_id;
        return $this;
    }

    public function getIgnoreUserId(): ?string
    {
        return $this->ignore_user_id ?? null;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $value = MobileVerification::normalizeNumber($value);

        /* @var IsANumberFormatValidator $mobile_number_format_validator */
        $mobile_number_format_validator = config('mobile-verification.mobile_number_format_validator');
        if (! (new $mobile_number_format_validator())->handle($this->getCountryCode(), $value)) {
            $fail(trans('mobile-verification::strings.validation.number.invalid', ['attribute' => $attribute]));
        }

        if ((! $this->can_send_otp) && is_null($this->should_be_registered_number)) {
            return;
        }

        $model_class = MobileVerification::mobileNumberModel();
        $mobile_number = $model_class::query()
                                     ->when($this->getIgnoreUserId(), function ($query) {
                                         $query->where('user_id', '!=', $this->ignore_user_id); // todo : test
                                     })
                                     ->hasPhoneNumber($this->getCountryCode(), $value, $this->user_type)
                                     ->first();

        if ($this->should_be_registered_number) {
            if (empty($mobile_number) || empty($mobile_number->user_id)) {
                $fail(trans('mobile-verification::strings.validation.number.doesnt-exist', ['attribute' => $attribute]));
            }
        }

        if (! $this->should_be_registered_number) {
            if ($mobile_number && $mobile_number->user_id) {
                $fail(trans('mobile-verification::strings.validation.number.exists', ['attribute' => $attribute]));
            }
        }

        if ($mobile_number && $this->can_send_otp) {
            if (! $mobile_number->can_request_code) {
                $fail(trans('mobile-verification::strings.validation.number.locked'));
            }

            if ($mobile_number->was_sent_recently) { // todo: test
                $fail(trans('mobile-verification::strings.validation.number.recently_sent'));
            }
        }
    }

    public function setData(array $data): static
    {
        $this->setCountryCode($data[$this->country_code_input_name] ?? null);
        return $this;
    }
}
