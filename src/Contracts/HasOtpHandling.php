<?php

namespace Javaabu\MobileVerification\Contracts;

interface HasOtpHandling
{
    public function getUserClass(): string;

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool;
}
