<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Javaabu\MobileVerification\Contracts\HasMobileNumberValidation;
use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;

abstract class MobileNumberVerificationController implements HasMobileNumberValidation
{
    use ValidatesMobileNumbers;

    abstract public function redirectUrl(): RedirectResponse|JsonResponse|View;

    public function mustBeARegisteredMobileNumber(array $request_data): bool
    {
        return false;
    }
}
