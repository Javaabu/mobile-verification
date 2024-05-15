<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Http\Request;

interface HasSessionMobileNumberContract
{
    public function setSessionMobileNumber(MobileNumber $mobile_number, Request $request): void;

    public function getSessionMobileNumberKey(): string;

    public function getSessionMobileNumberId(Request $request): ?string;

    public function getSessionMobileNumber(Request $request): ?MobileNumber;
}
