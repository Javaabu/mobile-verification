<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Http\Controllers\LoginController as BaseLoginController;

class LoginController extends BaseLoginController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';


    public function redirectAfterLogin(): RedirectResponse | JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'User logged in successfully']);
        }

        return redirect()->back()->with('success', 'User logged in successfully');
    }
}
