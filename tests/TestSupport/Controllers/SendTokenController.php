<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Traits\CanValidateMobileNumber;
use Javaabu\MobileVerification\Traits\CanSendVerificationCode;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;

class SendTokenController
{
    protected string $user_class = 'user';

    use CanSendVerificationCode;


}
