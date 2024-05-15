<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\View\View;
use Javaabu\MobileVerification\Traits\UpdatesMobileNumber;
use Javaabu\MobileVerification\Traits\SendsVerificationCode;
use Javaabu\MobileVerification\Traits\UsesSessionMobileNumber;
use Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract;
use Javaabu\MobileVerification\Contracts\SendVerificationCodeContract;
use Javaabu\MobileVerification\Contracts\VerifyVerificationCodeContract;
use Javaabu\MobileVerification\Contracts\HasSessionMobileNumberContract;
use Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class MobileNumberUpdateController implements
    SendVerificationCodeContract,
    VerifyVerificationCodeContract,
    HasSessionMobileNumberContract,
{
    use UpdatesMobileNumber;


    public function getVerificationCodeRequestFormView(): ?string
    {
        return "";
    }

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return false;
    }
}
