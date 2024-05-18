<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidVerificationCode;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Actions\AssociateUserWithMobileNumberAction;

trait CanRegisterUsingToken
{
    use HasUserGuard;
    use HasUserType;

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
            'number' => $data['number'],
            'country_code' => $data['country_code'] ?? null,
            'user_type' => $this->getUserType(),
            'user_id' => $user->id,
        ]);

        $mobileNumber = (new AssociateUserWithMobileNumberAction())->handle($mobile_number_data);

        $this->authenticateUser($user);

        return $this->redirectUrl();
    }

    protected function authenticateUser(HasMobileNumber $user): void
    {
        auth($this->getUserGuard())->login($user);
        session()->regenerate();
    }

    protected function validate(array $request_data)
    {
        return Validator::make($request_data, $this->getValidationRules($request_data));
    }

    public function redirectUrl(): RedirectResponse | JsonResponse
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

        $verification_code_validation_rules = [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number' => ['required', new IsValidMobileNumber($this->getUserType(), can_be_taken_by_user: false, can_send_otp: false)],
            'verification_code' => ['required', 'numeric', new IsValidVerificationCode($this->getUserType(), $number)],
        ];

        $user_data_validation_rules = $this->getUserDataValidationRules($request_data);

        return array_merge($verification_code_validation_rules, $user_data_validation_rules);
    }
}
