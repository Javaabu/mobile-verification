<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerifyMobileNumberAvailabilityController
{

    public function verify(Request $request)
    {
        $request = Validator::make([
            'mobile_number' => 'required|mobile_number'
        ]);
        return redirect()->back()->with('mobile_number', 'The mobile number is already in use.');
    }
}
