<?php

namespace Javaabu\MobileVerification\Contracts;

interface ShouldHaveVerifiedMobileNumber extends HasMobileNumber
{
    public function redirectToMobileVerificationUrl();
}
