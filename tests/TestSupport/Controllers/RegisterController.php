<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\RegistersWithMobileNumber;
use Javaabu\MobileVerification\Contracts\RegisterWithMobileNumberContract;

class RegisterController extends Controller implements RegisterWithMobileNumberContract
{
    use RegistersWithMobileNumber;

    public function getGuardName(): string
    {
        return 'web';
    }

    public function getUserClass(Request $request): string
    {
        return User::class;
    }

    public function createUser(array $data): HasMobileNumber
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return $user;
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

    public function getRegistrationFormView(): string
    {
        // TODO: Implement getRegistrationFormView() method.
    }

    public function enableReCaptcha(): bool
    {
        return false;
    }

    public function guardName(): string
    {
        return 'web';
    }

    public function userType(): \Javaabu\Auth\User
    {
        return new User();
    }
}
