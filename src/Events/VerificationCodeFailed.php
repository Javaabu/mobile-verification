<?php

namespace Javaabu\MobileVerification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * A wrong, expired or locked code was entered for a number
 */
class VerificationCodeFailed
{
    use Dispatchable;

    public function __construct(
        public MobileNumber $mobile_number,
        public string $reason,
    ) {}
}
