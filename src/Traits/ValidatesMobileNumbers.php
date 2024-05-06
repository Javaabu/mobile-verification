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

        if ($request->expectsJson()) {
            return response()->json(['message' => __('The mobile number is valid')]);
        }

        return redirect()->back();
    }

    public function getMobileNumberValidationRules(array $request_data): array
    {
        return [
            'country_code' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $country_codes = array_values(Countries::countryCodes());
                    if (! in_array($value, $country_codes)) {
                        $fail(trans('mobile-verification::strings.validation.country_code.invalid'));
                    }
                },
            ],
            'number'       => ['required', new IsValidMobileNumber($this->getUserClass(), data_get($request_data, 'country_code'))],
        ];
    }

    public function getMobileNumberValidationErrorMessages(): array
    {
        return [
            'number.required' => trans('mobile-verification::strings.validation.number.required', ['attribute' => 'number']),
        ];
    }

    public function getUserClass(): string
    {
        if (property_exists($this, 'user_class')) {
            return $this->user_class;
        }

        return 'user';
    }
}
