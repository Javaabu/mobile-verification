<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;

trait CanValidateMobileNumber
{
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
                    $this->getUserClass(),
                    data_get($request_data, 'country_code'),
                    false,
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

    public function getUserClass(): string
    {
        if (property_exists($this, 'user_class')) {
            return $this->user_class;
        }

        return 'user';
    }
}
