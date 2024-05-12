<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Http\Controllers\OTPController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class SendTokenController extends OTPController
{
    protected string $user_class = User::class;


    public function mustBeARegisteredMobileNumber(array $request_data): bool
    {
        return false;
    }
}
