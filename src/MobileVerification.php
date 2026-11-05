<?php

namespace Javaabu\MobileVerification;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\Auth\MobileLoginMethod;
use Javaabu\MobileVerification\Models\MobileNumber;

class MobileVerification
{
    public static bool $runs_migrations = true;

    /**
     * Stop the package from loading its migration, e.g. after publishing it
     */
    public static function ignoreMigrations(): void
    {
        static::$runs_migrations = false;
    }

    /**
     * Add mobile login (and registration) to javaabu/auth user types. Call it before Route::authRoutes().
     */
    public static function enableFor(string ...$types): void
    {
        foreach ($types as $type) {
            UserTypes::get($type)->loginMethod(MobileLoginMethod::class);
        }
    }

    /**
     * Whether a user type logs in with a mobile number
     */
    public static function enabledFor(UserType|string $type): bool
    {
        $type = is_string($type) ? UserTypes::get($type) : $type;

        foreach ($type->loginMethodsList() as $method) {
            if ($method instanceof MobileLoginMethod) {
                return true;
            }
        }

        return false;
    }

    public static function config(string $key = '', mixed $default = null): mixed
    {
        return config('mobile-verification' . ($key ? ".{$key}" : ''), $default);
    }

    /** @return class-string<MobileNumber> */
    public static function mobileNumberModel(): string
    {
        return static::config('mobile_number_model', MobileNumber::class);
    }

    public static function newMobileNumber(): MobileNumber
    {
        $model = static::mobileNumberModel();

        return new $model;
    }

    public static function defaultCountryCode(): string
    {
        return static::normalizeCountryCode((string) static::config('default_country_code', '960'), false);
    }

    /** @return array<int, string> */
    public static function allowedCountryCodes(): array
    {
        $codes = array_map(
            fn ($code) => static::normalizeCountryCode((string) $code, false),
            (array) static::config('allowed_country_codes', [static::defaultCountryCode()]),
        );

        return array_values(array_filter($codes));
    }

    public static function numberPrefix(): string
    {
        return (string) static::config('number_prefix', '+');
    }

    /**
     * The allowed country codes for a select: `['960' => '+960']`
     *
     * @return array<string, string>
     */
    public static function countryCodeOptions(): array
    {
        $options = [];

        foreach (static::allowedCountryCodes() as $code) {
            $options[$code] = static::numberPrefix() . $code;
        }

        return $options;
    }

    /**
     * A number as stored: digits only, without leading zeros
     */
    public static function normalizeNumber(?string $number): string
    {
        $number = preg_replace('/[\s\-().]/u', '', (string) $number);

        return ltrim($number, '0');
    }

    /**
     * A country code as stored: `+960` and `00960` become `960`; empty gives the default
     */
    public static function normalizeCountryCode(?string $country_code, bool $use_default = true): string
    {
        $country_code = ltrim(trim((string) $country_code), '+0');

        if ($country_code === '' && $use_default) {
            return static::defaultCountryCode();
        }

        return $country_code;
    }

    /**
     * The value of the `user_type` column for a user type's numbers: its model's morph class
     */
    public static function morphClassFor(UserType|Model|string $type): string
    {
        if ($type instanceof Model) {
            return $type->getMorphClass();
        }

        $type = is_string($type) ? UserTypes::get($type) : $type;

        return $type->newModel()->getMorphClass();
    }

    /**
     * The record of a number for a user type, if there is one
     */
    public static function find(UserType|Model|string $type, ?string $country_code, ?string $number): ?MobileNumber
    {
        $number = static::normalizeNumber($number);

        if ($number === '') {
            return null;
        }

        return static::mobileNumberModel()::query()
            ->hasPhoneNumber(static::normalizeCountryCode($country_code), $number, static::morphClassFor($type))
            ->first();
    }

    /**
     * The record of a number for a user type, created without an owner when there is none
     */
    public static function findOrCreate(UserType|Model|string $type, ?string $country_code, string $number): MobileNumber
    {
        $model = static::mobileNumberModel();

        return $model::query()->firstOrCreate([
            'country_code' => static::normalizeCountryCode($country_code),
            'number' => static::normalizeNumber($number),
            'user_type' => static::morphClassFor($type),
        ]);
    }

    /**
     * The user a number belongs to, loaded through the type's guard provider (the model that logs in)
     */
    public static function userFor(UserType $type, ?MobileNumber $mobile_number): ?Authenticatable
    {
        if (! $mobile_number?->hasOwner()) {
            return null;
        }

        if ($mobile_number->user_type !== static::morphClassFor($type)) {
            return null;
        }

        return $type->auth()->getProvider()->retrieveById($mobile_number->user_id);
    }

    public static function skipsTwoFactor(): bool
    {
        return (bool) static::config('skip_two_factor', false);
    }

    public static function allowsRegistration(UserType $type): bool
    {
        return static::config('registration', true)
            && $type->hasFeature(UserType::REGISTRATION);
    }
}
