<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;

class ValidateMobileNumbersController
{
    public function validate(Request $request)
    {
        $request = Validator::make(
            $request->all(),
            [
                'number' => ['required', new IsValidMobileNumber()],
            ],
            [
                'number.required' => trans('mobile-verification::strings.validation.number.required', ['attribute' => 'number']),
            ]
        );

        if ($request->fails()) {
            return redirect()->back()->with('number', $request->errors()->first());
        }

        return redirect()->back();
    }
}
