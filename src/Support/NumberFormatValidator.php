<?php

namespace Javaabu\MobileVerification\Support;

use Javaabu\MobileVerification\Contracts\ValidatesNumberFormat;

/**
 * Maldivian mobile numbers are 7 digits starting with 7 or 9. Other country codes get a loose
 * check of 4 to 15 digits (E.164 allows 15 with the country code); bind your own for stricter rules.
 */
class NumberFormatValidator implements ValidatesNumberFormat
{
    public function passes(string $country_code, string $number): bool
    {
        if ($country_code === '960') {
            return (bool) preg_match('/^[79]\d{6}$/', $number);
        }

        return (bool) preg_match('/^\d{4,15}$/', $number)
            && strlen($country_code . $number) <= 15;
    }
}
