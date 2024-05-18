<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Contracts\HasRequestMobileNumberContract;

/* @var HasRequestMobileNumberContract $this */
trait HasRequestMobileNumber
{
    public function getMobileNumberFromRequest(Request $request, bool $create = false): ?MobileNumber
    {
        $number = $request->input($this->getMobileNumberInputKey());
        $country_code = $request->input($this->getCountryCodeInputKey()) ?: MobileVerification::defaultCountryCode();

        $model_class = MobileVerification::mobileNumberModel();

        $mobile_number =  $model_class::query()
                           ->hasPhoneNumber($country_code, $number, $this->getUserType())
                           ->first();

        if (! $create || $mobile_number) {
            return $mobile_number;
        }

        $mobile_number = new $model_class();
        $mobile_number->number = $number;
        $mobile_number->country_code = $country_code;
        $mobile_number->user_type = $this->getUserType();
        $mobile_number->user_id = null;
        $mobile_number->save();

        return $mobile_number;
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
