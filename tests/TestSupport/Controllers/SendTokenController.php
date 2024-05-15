<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Contracts\SendVerificationCodeContract;
use Javaabu\MobileVerification\Http\Controllers\OTPController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanSendVerificationCode;

class SendTokenController implements SendVerificationCodeContract
{
    use CanSendVerificationCode;

    public function getUserClass(): string
    {
        return User::class;
    }

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        $purpose = data_get($request_data, 'purpose', null);
        if ($purpose === 'register') {
            return false;
        }

        if ($purpose === 'login') {
            return true;
        }

        return null;
    }
}
