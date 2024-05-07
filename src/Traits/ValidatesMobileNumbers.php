<?php

namespace Javaabu\MobileVerification\Traits;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Javaabu\MobileVerification\Support\Enums\Countries;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;

trait ValidatesMobileNumbers
{
    use CanValidateMobileNumber;

    public function validate(Request $request): RedirectResponse|JsonResponse
    {
        $rules = $this->getMobileNumberValidationRules($request->all());
        $messages = $this->getMobileNumberValidationErrorMessages();
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()->withErrors($validator->errors())->withInput();
        }

        return $this->redirectUrl();
    }

    public function redirectUrl(): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => __('The mobile number is valid')]);
        }

        return redirect()->back();
    }
}
