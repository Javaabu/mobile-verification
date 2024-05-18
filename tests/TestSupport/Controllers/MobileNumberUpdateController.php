<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Traits\UpdatesMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract;

class MobileNumberUpdateController implements UpdateMobileNumberContract
{
    use UpdatesMobileNumber;

    public function getVerificationCodeRequestFormView(): ?string
    {
        return "";
    }

    public function redirectAfterVerificationCodeRequest(MobileNumber $mobile_number, Request $request)
    {
        return $this->showVerificationCodeForm($request, $mobile_number);
    }

    public function getUserClass(): string
    {
        return User::class;
    }

    public function getVerificationCodeFormView(): ?string
    {
        // TODO: Implement getVerificationCodeFormView() method.
    }

    public function verificationCodeSuccessRedirectUrl(): string
    {
        // TODO: Implement verificationCodeSuccessRedirectUrl() method.
    }
}
