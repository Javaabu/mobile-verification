<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class MobileNumberUpdateController extends UpdateMobileNumberController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

}
