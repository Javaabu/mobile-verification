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

class MobileNumberUpdateController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number'       => ['required', new IsValidMobileNumber($this->user_class)],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $data = $validator->validated();

        dd($data);


    }

    public function update(Request $request): RedirectResponse | JsonResponse
    {
        $validator = $this->validate($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // Token is valid
        $data = $validator->validated();

        $user = Auth::guard($this->guard)->user();

        $country_code = $data['country_code'] ?? null;
        $phone_number = $data['number'] ?? null;
        $mobileNumber = (new AssociateUserWithMobileNumberAction($this->user_class, $country_code))->handle($user->id, $phone_number);

        return $this->redirectAfterRegistration();
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

}
