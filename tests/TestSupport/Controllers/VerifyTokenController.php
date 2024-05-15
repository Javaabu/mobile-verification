<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidVerificationCode;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class VerifyTokenController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

    public function verify(Request $request): RedirectResponse | JsonResponse
    {
        $validator = $this->validate($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // Token is valid
        return $this->redirectUrl();
    }

    public function redirectUrl(): RedirectResponse | JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Token verified successfully']);
        }

        return redirect()->back()->with('success', 'Token verified successfully');
    }

    public function validate(array $request_data)
    {
        return Validator::make($request_data, $this->getValidationRules($request_data));
    }

    public function getValidationRules(array $request_data): array
    {
        $number = $request_data['number'] ?? null;

        return [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number' => ['required', new IsValidMobileNumber($this->getUserType())],
            'token' => ['required', 'numeric', new IsValidVerificationCode($this->getUserType(), $number)],
        ];
    }

    public function getUserType(): string
    {
        return (new $this->user_class())->getMorphClass();
    }
}
