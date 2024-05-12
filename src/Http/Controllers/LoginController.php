<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Traits\HasFormView;
use Javaabu\MobileVerification\Traits\HasUserGuard;
use Javaabu\MobileVerification\Traits\HasUserType;

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

        // Login the user
        Auth::guard($this->guard)->login($mobile_number->user, true);
        $request->session()->regenerate();

        return $this->redirectAfterLogin();
    }


    public function redirectUrlOnValidationError(Request $request, \Illuminate\Validation\Validator $validator): RedirectResponse|JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return redirect()->back()->withErrors($validator->errors())->withInput();
    }

    public function redirectAfterLogin(): RedirectResponse|JsonResponse
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
            'token' => ['required', 'numeric', new IsValidToken($this->getUserType(), $number)],
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
