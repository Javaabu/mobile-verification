<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;
use Javaabu\MobileVerification\Contracts\HasMobileNumberValidation;

abstract class MobileNumberVerificationController implements HasMobileNumberValidation
{
    use ValidatesMobileNumbers;

    abstract public function redirectUrl(): RedirectResponse|JsonResponse|View;

    public function mustBeARegisteredMobileNumber(array $request_data): bool
    {
        return false;
    }
}
