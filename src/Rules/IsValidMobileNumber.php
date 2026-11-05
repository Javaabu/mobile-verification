<?php

namespace Javaabu\MobileVerification\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Contracts\ValidatesNumberFormat;
use Javaabu\MobileVerification\MobileVerification;

/**
 * A mobile number in the right format for its country code (read from the `country_code` input),
 * optionally one that is, or isn't yet, a user's verified number:
 *
 * ```php
 * 'number' => ['required', 'string', (new IsValidMobileNumber('user'))->notRegistered()->ignore($user)],
 * ```
 */
class IsValidMobileNumber implements DataAwareRule, ValidationRule
{
    protected ?bool $should_be_registered = null;

    protected ?string $country_code = null;

    protected int|string|null $ignore_user_id = null;

    /**
     * @param  UserType|string|null  $user_type  a javaabu/auth user type, or a model's morph class with `$is_morph_class`
     */
    public function __construct(
        protected UserType|string|null $user_type = null,
        protected string $country_code_input_name = 'country_code',
        protected bool $is_morph_class = false,
    ) {}

    public function registered(): static
    {
        $this->should_be_registered = true;

        return $this;
    }

    public function notRegistered(): static
    {
        $this->should_be_registered = false;

        return $this;
    }

    public function setShouldBeRegisteredNumber(?bool $value): static
    {
        $this->should_be_registered = $value;

        return $this;
    }

    public function setCountryCode(?string $country_code): static
    {
        $this->country_code = MobileVerification::normalizeCountryCode($country_code);

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->country_code ?? MobileVerification::defaultCountryCode();
    }

    /**
     * Let a user keep their own number
     */
    public function ignore(Model|int|string|null $user): static
    {
        $this->ignore_user_id = $user instanceof Model ? $user->getKey() : $user;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value)) {
            $fail(__('The :attribute is not a valid mobile number.'));

            return;
        }

        $number = MobileVerification::normalizeNumber((string) $value);

        if ($number === '') {
            return;
        }

        /** @var ValidatesNumberFormat $format */
        $format = app(MobileVerification::config('number_format_validator', ValidatesNumberFormat::class));

        if (! $format->passes($this->getCountryCode(), $number)) {
            $fail(__('The :attribute is not a valid mobile number.'));

            return;
        }

        if ($this->should_be_registered === null || $this->user_type === null) {
            return;
        }

        $morph_class = $this->is_morph_class && is_string($this->user_type)
            ? $this->user_type
            : MobileVerification::morphClassFor($this->user_type);

        $owned = MobileVerification::mobileNumberModel()::query()
            ->hasPhoneNumberWithOwner($this->getCountryCode(), $number, $morph_class)
            ->when($this->ignore_user_id !== null, fn ($query) => $query->where('user_id', '!=', $this->ignore_user_id))
            ->exists();

        if ($this->should_be_registered && ! $owned) {
            $fail(__('There is no account with this mobile number.'));
        }

        if (! $this->should_be_registered && $owned) {
            $fail(__('This mobile number is already registered.'));
        }
    }

    public function setData(array $data): static
    {
        $this->setCountryCode(isset($data[$this->country_code_input_name]) && is_scalar($data[$this->country_code_input_name])
            ? (string) $data[$this->country_code_input_name]
            : null);

        return $this;
    }
}
