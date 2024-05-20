<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Traits\UpdatesMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract;

class MobileNumberUpdateController extends Controller implements UpdateMobileNumberContract
{
    use UpdatesMobileNumber;

    public function getUserClass(): string
    {
        return User::class;
    }

    public function getVerificationCodeRequestFormView(): ?string
    {
        return "verification-code-request-form";
    }

    public function getVerificationCodeFormView(): ?string
    {
        return "verification-code-entry-form";
    }

    public function verificationCodeSuccessRedirectUrl(): string
    {
        return '/mobile-verification/updated';
    }
}
