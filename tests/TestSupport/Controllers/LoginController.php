<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\MobileVerification\Http\Controllers\LoginController as BaseLoginController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

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
