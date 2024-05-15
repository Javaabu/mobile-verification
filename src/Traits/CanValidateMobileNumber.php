<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Support\Enums\Countries;

trait CanValidateMobileNumber
{
    use HasUserType;

    public function getMobileNumberValidationRules(array $request_data): array
    {
        return [
            'country_code' => [
                'nullable',
                new IsValidCountryCode(),
            ],
            'number' => [
                'required',
                (new IsValidMobileNumber($this->getUserType()))
                    ->setShouldBeRegisteredNumber($this->mustBeARegisteredMobileNumber($request_data)),
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
