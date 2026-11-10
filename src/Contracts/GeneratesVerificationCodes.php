<?php

namespace Javaabu\MobileVerification\Contracts;

interface GeneratesVerificationCodes
{
    /**
     * A new code to send by SMS
     */
    public function generate(): string;
}
