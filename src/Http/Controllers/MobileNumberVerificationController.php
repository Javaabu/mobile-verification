<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;

abstract class MobileNumberVerificationController
{
    use ValidatesMobileNumbers;

    abstract public function redirectUrl(): RedirectResponse|JsonResponse|View;
}
