<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;

class VerifyTokenController
{
    public function verify(Request $request)
    {
        dd('verify token');
    }
}
