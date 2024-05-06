<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Traits\CanValidateMobileNumber;

class RegisterController
{
    use CanValidateMobileNumber;
    protected string $user_class = 'user';

    public function register(Request $request)
    {
        $rules = $this->getMobileNumberValidationRules($request->all());
        $messages = $this->getMobileNumberValidationErrorMessages();
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        $mobile_number_data = MobileNumberData::fromRequestData(array_merge($request->all(), [
            'user_type' => $this->user_class,
        ]));

        $mobile_number = (new MobileNumberService())->store($mobile_number_data);

        //generate the token
        $token = $mobile_number->generateToken();

        // Send OTP
        $this->sendSmsNotification($token, $mobile_number);

        if ($request->expectsJson()) {
            return response()->json(['message' => __('The mobile number is valid')]);
        }

        return redirect()->back();
    }

    protected function sendSmsNotification($token, $phone): void
    {
        $user_name = $phone->user ? $phone->user->name : '';
        $phone->notify(new MobileNumberVerificationToken($token, $user_name));
    }

}
