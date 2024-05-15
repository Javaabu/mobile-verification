<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Contracts\MobileNumber;

trait UsesSessionMobileNumber
{

    public function getSessionMobileNumberId(Request $request): ?string
    {
        return $request->session()->get($this->getSessionMobileNumberKey());
    }

    public function getSessionMobileNumber(Request $request): ?MobileNumber
    {
        $mobileNumberId = $this->getSessionMobileNumberId($request);

        if (! $mobileNumberId) {
            return null;
        }

        return MobileVerification::findMobileNumberById($mobileNumberId);
    }

    public function setSessionMobileNumber(MobileNumber $mobile_number, Request $request): void
    {
        $request->session()->put($this->getSessionMobileNumberKey(), $mobile_number->id);
    }

}
