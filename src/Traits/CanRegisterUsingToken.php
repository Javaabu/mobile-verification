<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Actions\AssociateUserWithMobileNumberAction;

trait CanRegisterUsingToken
{
    public function register(Request $request): RedirectResponse | JsonResponse
    {
        $validator = $this->validate($request->all());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        // Token is valid
        $data = $validator->validated();

        $user = $this->registerUser($data);

        $mobile_number_data = MobileNumberData::fromRequestData([
            'number'     => $data['number'],
            'country_code' => $data['country_code'] ?? null,
            'user_type'  => $this->user_class,
            'user_id'    => $user->id,
        ]);

        $mobileNumber = (new AssociateUserWithMobileNumberAction)->handle($mobile_number_data);

        return $this->redirectAfterRegistration();
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
            'number'       => ['required', new IsValidMobileNumber($this->user_class, can_be_taken_by_user: false, can_send_otp: true)],
            'token'        => ['required', 'numeric', new IsValidToken($this->user_class, $number)],
            'name'         => ['required'],
            'email'        => ['required', 'email'],
        ];
    }
}