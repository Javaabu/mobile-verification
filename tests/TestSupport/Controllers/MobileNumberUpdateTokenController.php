<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Http\Controllers\OTPController as BaseOTPController;

class MobileNumberUpdateTokenController extends BaseOTPController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

    public function redirectUrl(Request $request): RedirectResponse|JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('A verification code has been sent to your mobile number. Please enter the code to verify your mobile number.')]);
        }

        return redirect()->back()->with('success', __('A verification code has been sent to your mobile number. Please enter the code to verify your mobile number.'));
    }

    public function mustBeARegisteredMobileNumber(array $request_data): bool
    {
        return false;
    }
}
