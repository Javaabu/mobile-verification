<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;

trait CanSendVerificationCode
{
    use CanValidateMobileNumber;

    public function mobileNumberOtp(Request $request): RedirectResponse|JsonResponse
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
            'user_type' => $this->getUserType(),
        ]));

        $mobile_number = (new MobileNumberService())->firstOrCreate($mobile_number_data);

        //generate the token
        $token = $mobile_number->generateToken();

        // Send OTP
        $this->sendSmsNotification($token, $mobile_number);

        return $this->redirectUrl($request);
    }

    protected function sendSmsNotification($token, $phone): void
    {
        $user_name = $phone->user ? $phone->user->name : '';
        $phone->notify(new MobileNumberVerificationToken($token, $user_name));
    }

    public function redirectUrl(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('A verification code has been sent to your mobile number. Please enter the code to verify your mobile number.')]);
        }

        return redirect()->back()->with('success', __('A verification code has been sent to your mobile number. Please enter the code to verify your mobile number.'));
    }
}
