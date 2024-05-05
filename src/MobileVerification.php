<?php

namespace Javaabu\MobileVerification;

use Illuminate\Http\Request;

class MobileVerification
{
    /**
     * Indicates if migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Configure to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static();
    }

    /**
     * Get the configs
     * @param string $key
     * @return mixed
     */
    public static function config(string $key = '')
    {
        $key = $key ? '.'.$key : '';

        return config('mobile-verification'.$key);
    }


    /**
     * Whether to verify
     *
     * @return bool
     */
    public static function shouldVerify(): bool
    {
        return self::config('verified');
    }

    /**
     * Default country code
     *
     * @return string
     */
    public static function mobileNumberModel(): string
    {
        return self::config('mobile_number_model');
    }

    /**
     * Default country code
     *
     * @return string
     */
    public static function defaultCountryCode(): string
    {
        return self::config('default_country_code');
    }

    /**
     * Allowed country codes
     *
     * @return string[]
     */
    public static function allowedCountryCodes(): array
    {
        return self::config('allowed_country_codes');
    }

    /**
     * Default prefix
     *
     * @return string
     */
    public static function defaultPrefix(): string
    {
        return self::config('number_prefix').self::defaultCountryCode();
    }

    /**
     * List the allowed country codes
     *
     * @param ?array $allowed_codes
     * @param ?string $number_prefix
     * @return array
     */
    public static function listAllowedCountryCodes(?array $allowed_codes = null, ?string $number_prefix = null): array
    {
        if (is_null($allowed_codes)) {
            $allowed_codes = self::allowedCountryCodes();
        }

        if (is_null($number_prefix)) {
            $number_prefix = self::config('number_prefix');
        }

        $list = [];

        foreach ($allowed_codes as $code) {
            $list[$code] = $number_prefix.$code;
        }

        return $list;
    }

    /**
     * Normalize mobile number
     *
     * @param string $number
     * @return string
     */
    public static function normalizeNumber(string $number): string
    {
        return ltrim($number, '0');
    }

    /**
     * Normalize mobile number
     *
     * @param Request $request
     * @param string $key
     * @return Request
     */
    public static function normalizePhoneNumberRequest(Request $request, string $key = 'phone'): Request
    {
        $number = $request->input($key);

        if ($number) {
            $number = self::normalizeNumber($number);
            $request->merge([$key => $number]);
        }

        return $request;
    }
}
