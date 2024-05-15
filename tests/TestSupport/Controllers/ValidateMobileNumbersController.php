<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Http\Controllers\MobileNumberVerificationController as BaseMobileNumberVerificationController;

class ValidateMobileNumbersController extends BaseMobileNumberVerificationController
{
    protected string $user_class = User::class;

    public function redirectUrl(): RedirectResponse|JsonResponse|View
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => __('The mobile number is valid')]);
        }

        return redirect()->back();
    }
}
