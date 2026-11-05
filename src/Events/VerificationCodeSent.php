<?php

namespace Javaabu\MobileVerification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * A code was sent to a number. The code itself isn't in the event.
 */
class VerificationCodeSent
{
    use Dispatchable;

    public function __construct(
        public MobileNumber $mobile_number,
        public Purposes $purpose,
    ) {}
}
