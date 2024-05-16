<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Contracts\RegisterWithMobileNumberContract;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\RegistersWithMobileNumber;

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

    public function getRegistrationFormView(): string
    {
        // TODO: Implement getRegistrationFormView() method.
    }
}
