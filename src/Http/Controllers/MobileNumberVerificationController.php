<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;

abstract class MobileNumberVerificationController
{
    use ValidatesMobileNumbers;

    abstract public function redirectUrl(): RedirectResponse|JsonResponse|View;
}
