<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;

class ValidateMobileNumbersController
{
    use ValidatesMobileNumbers;

    protected string $user_class = 'user';
}
