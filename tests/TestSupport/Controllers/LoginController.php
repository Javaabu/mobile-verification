<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Contracts\LoginWithMobileNumberContract;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\LoginsWithMobileNumber;

class LoginController extends Controller implements LoginWithMobileNumberContract
{
    use LoginsWithMobileNumber;

    public function __construct()
    {
        $this->middleware('guest:' . $this->getGuardName());
    }

    public function getUserClass(): string
    {
        return User::class;
    }

    public function getGuardName(): string
    {
        return 'web';
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
        // TODO: Implement verificationCodeSuccessRedirectUrl() method.
    }

    public function enableReCaptcha(): bool
    {
        return false;
    }
}
