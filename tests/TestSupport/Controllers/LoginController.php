<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Traits\HasUserType;
use Javaabu\MobileVerification\Traits\SendsVerificationCode;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\HasRequestMobileNumber;
use Javaabu\MobileVerification\Traits\LoginsWithMobileNumber;
use Javaabu\MobileVerification\Traits\UsesSessionMobileNumber;
use Javaabu\MobileVerification\Traits\VerifiesVerificationCode;
use Javaabu\MobileVerification\Contracts\LoginWithMobileNumberContract;

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
        // TODO: Implement getVerificationCodeRequestFormView() method.
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
