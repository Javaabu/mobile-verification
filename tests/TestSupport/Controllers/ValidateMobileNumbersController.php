<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;

class ValidateMobileNumbersController
{
    use ValidatesMobileNumbers;

    protected string $user_class = 'user';
}
