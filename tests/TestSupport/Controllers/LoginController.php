<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Traits\CanRegisterUsingToken;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanValidateMobileNumber;
use Javaabu\MobileVerification\Contracts\IsRegistrationController;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Support\Actions\AssociateUserWithMobileNumberAction;

class LoginController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

    public function login(Request $request): RedirectResponse | JsonResponse
    {
        $validator = $this->validate($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // Token is valid
        $data = $validator->validated();

        $mobile_number_data = MobileNumberData::fromRequestData([
            'country_code' => $data['country_code'] ?? null,
            'number'       => $data['number'],
            'user_type'    => $this->getUserType(),
        ]);

        $mobile_number = (new MobileNumberService())->getMobileNumber($mobile_number_data);

        Auth::guard($this->guard)->login($mobile_number->user, true);
        $request->session()->regenerate();

        return $this->redirectAfterLogin();
    }

    public function redirectAfterLogin(): RedirectResponse | JsonResponse
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
            'number'       => ['required', new IsValidMobileNumber($this->user_class)],
            'token'        => ['required', 'numeric', new IsValidToken($this->getUserType(), $number)],
        ];
    }

    public function getUserType(): string
    {
        return (new $this->user_class)->getMorphClass();
    }
}