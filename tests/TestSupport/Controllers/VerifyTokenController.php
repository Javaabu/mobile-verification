<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerifyTokenController
{

    public function verify(Request $request)
    {
        dd('verify token');
    }
}
