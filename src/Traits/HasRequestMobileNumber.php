<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Contracts\HasRequestMobileNumberContract;

/* @var HasRequestMobileNumberContract $this */
trait HasRequestMobileNumber
{
    public function getMobileNumberFromRequest(Request $request): ?MobileNumber
    {
        $mobile_number = $request->input($this->getMobileNumberInputKey());
        $country_code = $request->input($this->getCountryCodeInputKey()) ?: MobileVerification::defaultCountryCode();

        $model_class = MobileVerification::mobileNumberModel();
        return $model_class::query()
                           ->hasPhoneNumber($country_code, $mobile_number, $this->getUserType())
                           ->first();
    }

    public function getMobileNumberInputKey(): string
    {
        return 'number';
    }

    public function getCountryCodeInputKey(): string
    {
        return 'country_code';
    }

    public function getVerificationCodeInputKey(): string
    {
        return 'verification_code';
    }

    public function getVerificationCodeIdInputKey(): string
    {
        return 'verification_code_id';
    }
}
