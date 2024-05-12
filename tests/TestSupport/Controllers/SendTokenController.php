<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanSendVerificationCode;
use Javaabu\MobileVerification\Http\Controllers\OTPController;

class SendTokenController extends OTPController
{
    protected string $user_class = User::class;


    public function mustBeARegisteredMobileNumber(array $request_data): bool
    {
        return false;
    }
}
