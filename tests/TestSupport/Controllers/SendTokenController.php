<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanSendVerificationCode;

class SendTokenController
{
    use CanSendVerificationCode;
    protected string $user_class = User::class;


}
