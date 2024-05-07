<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\CanValidateMobileNumber;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Support\Actions\AssociateUserWithMobileNumberAction;

class RegisterController
{
    protected string $user_class = 'user';


    public function register(Request $request): RedirectResponse | JsonResponse
    {
        $validator = $this->validate($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // Token is valid

        $data = $validator->validated();

        $user = $this->registerUser($data);

        $country_code = $data['country_code'] ?? null;
        $phone_number = $data['number'] ?? null;
        $mobileNumber = (new AssociateUserWithMobileNumberAction($this->user_class, $country_code))->handle($user->id, $phone_number);

        $this->redirectAfterRegistration();
    }

    public function registerUser(array $data): User
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return $user;
    }

    protected function validate(array $request_data)
    {
        return Validator::make($request_data, $this->getValidationRules($request_data));
    }

    public function redirectAfterRegistration(): RedirectResponse | JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'User registered successfully']);
        }

        return redirect()->back()->with('success', 'User registered successfully');
    }

    /**
     * @param array $request_data
     * @return array
     */
    public function getValidationRules(array $request_data): array
    {
        $number = $request_data['number'] ?? null;
        return [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number'       => ['required', new IsValidMobileNumber($this->user_class)],
            'token'        => ['required', 'numeric', new IsValidToken($this->user_class, $number)],
            'name'         => ['required'],
            'email'        => ['required', 'email'],
        ];
    }

}
