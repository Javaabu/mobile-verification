<?php

namespace Javaabu\MobileVerification\Contracts;

use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

interface IsRegistrationController
{

    public function registerUser(array $data): HasMobileNumber;
}
