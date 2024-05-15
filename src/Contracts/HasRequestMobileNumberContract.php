<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Http\Request;

interface HasRequestMobileNumberContract extends HasUserTypeContract
{
    public function getMobileNumberFromRequest(Request $request): ?MobileNumber;

    public function getMobileNumberInputKey(): string;

    public function getCountryCodeInputKey(): string;

    public function getVerificationCodeInputKey(): string;

    public function getVerificationCodeIdInputKey(): string;

}
