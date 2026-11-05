<?php

namespace Javaabu\MobileVerification\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * A number got too many wrong codes and is locked for `attempt_expiry` minutes
 */
class MobileNumberLocked
{
    use Dispatchable;

    public function __construct(
        public MobileNumber $mobile_number,
    ) {}
}
