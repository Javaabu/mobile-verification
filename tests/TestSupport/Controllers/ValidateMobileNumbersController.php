<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Javaabu\MobileVerification\Http\Controllers\MobileNumberVerificationController as BaseMobileNumberVerificationController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

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
