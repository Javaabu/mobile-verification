<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class MobileNumberUpdateTokenController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

    public function requestOtp(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number' => ['required', new IsValidMobileNumber($this->getUserType(), can_be_taken_by_user: false)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $data = $validator->validated();

        $mobile_number_data = MobileNumberData::fromRequestData([
            'number' => $data['number'],
            'country_code' => $data['country_code'] ?? null,
            'user_type' => $this->getUserType(),
            'user_id' => null,
        ]);

        $mobile_number = (new MobileNumberService())->firstOrCreate($mobile_number_data);
        $token = $mobile_number->generateToken();
        $this->sendSmsNotification($token, $mobile_number);

        return $this->redirectAfterOtpUrl($request);
    }

    public function redirectAfterOtpUrl(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('A verification code has been sent to your mobile number. Please enter the code to verify your mobile number.')]);
        }

        return redirect()->back()->with('success', __('A verification code has been sent to your mobile number. Please enter the code to verify your mobile number.'));
    }

    protected function sendSmsNotification($token, $phone): void
    {
        $user_name = $phone->user ? $phone->user->name : '';
        $phone->notify(new MobileNumberVerificationToken($token, $user_name));
    }

    public function getUserType(): string
    {
        return (new $this->user_class())->getMorphClass();
    }

}
