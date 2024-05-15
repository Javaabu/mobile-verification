<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\StatefulGuard;
use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\RegistersWithMobileNumber;
use Javaabu\MobileVerification\Contracts\SendVerificationCodeContract;
use Javaabu\MobileVerification\Http\Controllers\RegistrationController;
use Javaabu\MobileVerification\Contracts\RegisterWithMobileNumberContract;

class RegisterController extends Controller implements RegisterWithMobileNumberContract
{
    use RegistersWithMobileNumber;

    public function __construct()
    {
        $this->applyMiddlewares();
    }

    public function getGuardName(): string
    {
        return 'web';
    }

    public function getUserClass(): string
    {
        return User::class;
    }

    public function createUser(array $data): HasMobileNumber
    {
//        return User::create($data);
    }

    public function getRegisterFieldsValidationRules(Request $request): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ];
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
