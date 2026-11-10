<?php

namespace Javaabu\MobileVerification\Contracts;

interface ValidatesNumberFormat
{
    /**
     * Whether a normalized number (digits only, no leading zeros) is a valid mobile number for the country code
     */
    public function passes(string $country_code, string $number): bool;
}
