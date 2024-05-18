<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Traits\HasFormView;
use Javaabu\MobileVerification\Traits\HasUserType;
use Javaabu\MobileVerification\Traits\HasUserGuard;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidVerificationCode;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;

abstract class LoginController
{
    use HasFormView;
    use HasUserGuard;
    use HasUserType;

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $validator = $this->validate($request->all());

        if ($validator->fails()) {
            return $this->redirectUrlOnValidationError($request, $validator);
        }

        // Token is valid
        $data = $validator->validated();

        $mobile_number_data = MobileNumberData::fromRequestData([
            'country_code' => $data['country_code'] ?? null,
            'number' => $data['number'],
            'user_type' => $this->getUserType(),
        ]);

        $mobile_number = (new MobileNumberService())->getMobileNumber($mobile_number_data);

        if ($request->expectsJson()) {
            $verification_code = $mobile_number->user?->createToken('api-verification_code');

            return response()->json([
                'message' => 'User logged in successfully',
                'verification_code' => $verification_code->plainTextToken,
                'expires_at' => $verification_code->accessToken->created_at->addMinutes(config('sanctum.expiration')),
            ]);
        }

        // Login the user
        Auth::guard($this->guard)->login($mobile_number->user, true);
        $request->session()->regenerate();

        return $this->redirectUrl();
    }


    public function redirectUrlOnValidationError(Request $request, \Illuminate\Validation\Validator $validator): RedirectResponse|JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return redirect()->back()->withErrors($validator->errors())->withInput();
    }

    public function redirectUrl(): RedirectResponse|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'User logged in successfully']);
        }

        return redirect()->back()->with('success', 'User logged in successfully');
    }

    protected function validate(array $request_data)
    {
        return Validator::make($request_data, $this->getValidationRules($request_data));
    }

    public function getValidationRules(array $request_data): array
    {
        $number = $request_data['number'] ?? null;

        return [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number' => ['required', new IsValidMobileNumber($this->getUserType(), can_be_taken_by_user: true)],
            'verification_code' => ['required', 'numeric', new IsValidVerificationCode($this->getUserType(), $number)],
        ];
    }

    public function getUserType(): string
    {
        return (new $this->user_class())->getMorphClass();
    }

    public function showRegistrationForm(): View
    {
        return $this->getFormView();
    }
}
