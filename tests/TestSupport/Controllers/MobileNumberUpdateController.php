<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController;

class MobileNumberUpdateController extends UpdateMobileNumberController
{
    protected string $user_class = User::class;
    protected string $guard = 'web';

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number' => ['required', new IsValidMobileNumber($this->getUserType(), can_be_taken_by_user: false)],
            'token' => ['required', new IsValidToken($this->getUserType(), $request->input('number'))],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $data = $validator->validated();

        $mobile_number_data = MobileNumberData::fromRequestData([
            'number' => $data['number'],
            'country_code' => $data['country_code'] ?? null,
            'user_type' => $this->getUserType(),
        ]);

        /* @var HasMobileNumber $user */
        $user = Auth::guard($this->guard)->user();
        $previous_number = $user->phone;

        $mobile_number = (new MobileNumberService())->getMobileNumber($mobile_number_data);
        (new MobileNumberService())->updateMobileNumber(
            $mobile_number,
            $previous_number,
            $user
        );

        return $this->redirectUrl($request);
    }

    public function redirectUrl(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('Mobile number updated successfully.')]);
        }

        return redirect()->back()->with('success', __('Mobile number updated successfully.'));
    }

    public function getUserType(): string
    {
        return (new $this->user_class())->getMorphClass();
    }
}
