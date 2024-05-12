<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\View\View;
use Javaabu\MobileVerification\Contracts\HasMobileNumberValidation;
use Javaabu\MobileVerification\Traits\CanSendVerificationCode;
use Javaabu\MobileVerification\Traits\HasFormView;

abstract class OTPController implements HasMobileNumberValidation
{
    use CanSendVerificationCode;
    use HasFormView;

    public function showOtpRequestForm(): View
    {
        return $this->getFormView();
    }
}
