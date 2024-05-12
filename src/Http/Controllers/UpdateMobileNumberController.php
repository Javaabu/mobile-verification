<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Traits\HasFormView;
use Javaabu\MobileVerification\Traits\HasUserGuard;
use Javaabu\MobileVerification\Traits\HasUserType;

abstract class UpdateMobileNumberController
{

}
