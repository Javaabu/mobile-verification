<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

trait ValidatesMobileNumbers
{
    use CanValidateMobileNumber;

    public function validate(Request $request): RedirectResponse|JsonResponse|View
    {
        $rules = $this->getMobileNumberValidationRules($request->all());
        $messages = $this->getMobileNumberValidationErrorMessages();
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->redirectUrlOnValidationError($request, $validator);
        }

        return $this->redirectUrl();
    }

    public function redirectUrlOnValidationError(Request $request, \Illuminate\Validation\Validator $validator): RedirectResponse|JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return redirect()->back()->withErrors($validator->errors())->withInput();
    }
}
