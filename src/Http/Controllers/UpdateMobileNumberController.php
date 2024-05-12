<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidToken;
use Javaabu\MobileVerification\Support\DataObjects\MobileNumberData;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Support\Services\MobileNumberService;
use Javaabu\MobileVerification\Traits\HasFormView;
use Javaabu\MobileVerification\Traits\HasUserType;

abstract class UpdateMobileNumberController
{
    use HasUserType;
    use HasFormView;

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'country_code' => ['nullable', 'numeric', 'in:' . Countries::getCountryCodesString()],
            'number' => ['required', new IsValidMobileNumber($this->getUserType(), can_be_taken_by_user: false)],
            'token' => ['required', new IsValidToken($this->getUserType(), $request->input('number'))],
        ]);

        if ($validator->fails()) {
            return $this->redirectUrlOnValidationError($request, $validator);
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

    public function showUpdateForm(Request $request): View
    {
        return $this->getFormView();
    }

    public function redirectUrl(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => __('Mobile number updated successfully.')]);
        }

        return redirect()->back()->with('success', __('Mobile number updated successfully.'));
    }

    public function redirectUrlOnValidationError(Request $request, \Illuminate\Validation\Validator $validator): RedirectResponse|JsonResponse|View
    {
        return redirect()->back()->withErrors($validator->errors());
    }
}
