<?php


namespace Javaabu\MobileVerification\Http\Controllers;


use Illuminate\View\View;
use Javaabu\MobileVerification\Traits\HasFormView;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanValidateMobileNumber;
use Javaabu\MobileVerification\Traits\CanSendVerificationCode;
use Javaabu\MobileVerification\Contracts\HasMobileNumberValidation;

abstract class OTPController implements HasMobileNumberValidation
{
    use CanSendVerificationCode;
    use HasFormView;

    public function showOtpRequestForm(): View
    {
        return $this->getFormView();
    }
}
