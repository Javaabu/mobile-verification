<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Traits\UpdatesMobileNumber;
use Javaabu\MobileVerification\Traits\SendsVerificationCode;
use Javaabu\MobileVerification\Traits\UsesSessionMobileNumber;
use Javaabu\MobileVerification\Contracts\SendVerificationCodeContract;
use Javaabu\MobileVerification\Contracts\VerifyVerificationCodeContract;
use Javaabu\MobileVerification\Contracts\HasSessionMobileNumberContract;
use Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class MobileNumberUpdateController implements
    SendVerificationCodeContract,
    VerifyVerificationCodeContract,
    HasSessionMobileNumberContract
{
    use UpdatesMobileNumber;

    public function getVerificationCodeRequestFormView(): ?string
    {
        return "";
    }

    public function redirectAfterVerificationCodeRequest(MobileNumber $mobile_number, Request $request)
    {
        return $this->showVerificationCodeForm($mobile_number);
    }

    public function getUserClass(): string
    {
        return User::class;
    }

    public function getVerificationCodeFormView(): ?string
    {
        // TODO: Implement getVerificationCodeFormView() method.
    }

    public function doAfterVerificationCodeVerified(MobileNumber $mobile_number, Request $request): mixed
    {
        /* @var HasMobileNumber $user */
         $user = $request->user();
         $user->updatePhone($mobile_number);

         return $user;
    }

    public function verificationCodeSuccessRedirectUrl(): string
    {
        // TODO: Implement verificationCodeSuccessRedirectUrl() method.
    }
}
