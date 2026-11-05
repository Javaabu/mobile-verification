<?php

namespace Javaabu\MobileVerification\Support;

use Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes;
use Javaabu\MobileVerification\MobileVerification;

/**
 * Random numeric codes of `mobile-verification.code_length` digits, from a cryptographically secure source
 */
class VerificationCodeGenerator implements GeneratesVerificationCodes
{
    public function generate(): string
    {
        $length = max(4, (int) MobileVerification::config('code_length', 6));

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}
