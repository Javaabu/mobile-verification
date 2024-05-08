<?php

namespace Javaabu\MobileVerification\Contracts;

interface IsRegistrationController
{
    public function registerUser(array $data): HasMobileNumber;
}
