<?php

namespace Javaabu\MobileVerification\Support;

use Javaabu\MobileVerification\Contracts\IsANumberFormatValidator;

class MobileNumberFormatValidator implements IsANumberFormatValidator
{
    /*
     * Returns true if the number is in the correct format
     * */
    public function handle(string $country_code, string $number): bool
    {
        if ($country_code != config('mobile-verification.default_country_code')) {
            return true;
        }

        // using preg_match to check if the number is in the correct format
        // the number must be 7 digits long
        // the number must either start with 7 or 9
        return preg_match('/^[79]\d{6}$/', $number);
    }
}
