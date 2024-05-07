<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Traits\CanRegisterUsingToken;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanValidateMobileNumber;
use Javaabu\MobileVerification\Contracts\IsRegistrationController;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Support\Actions\AssociateUserWithMobileNumberAction;

class RegisterController implements IsRegistrationController
{
    use CanRegisterUsingToken;

    protected string $user_class = 'user';

    public function registerUser(array $data): HasMobileNumber
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return $user;
    }

}
