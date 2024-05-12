<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Contracts\IsRegistrationController;
use Javaabu\MobileVerification\Http\Controllers\RegistrationController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class RegisterController extends RegistrationController implements IsRegistrationController
{
    protected string $user_class = User::class;

    protected string $user_guard = 'web';

    public function registerUser(array $data): HasMobileNumber
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return $user;
    }

    public function getUserDataValidationRules(array $request_data): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email'],
        ];
    }
}
