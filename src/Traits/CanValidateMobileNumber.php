<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;

trait CanValidateMobileNumber
{
    use CanGetUserType;

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
            'number' => [
                'required',
                new IsValidMobileNumber(
                    $this->getUserType(),
                    data_get($request_data, 'country_code'),
                    $this->mustBeARegisteredMobileNumber($request_data),
                ),
            ],
        ];
    }

    public function getMobileNumberValidationErrorMessages(): array
    {
        return [
            'number.required' => trans('mobile-verification::strings.validation.number.required', ['attribute' => 'number']),
        ];
    }
}
