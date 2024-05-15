<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;

interface LoginWithMobileNumberContract extends
    SendVerificationCodeContract,
    VerifyVerificationCodeContract,
    HasSessionMobileNumberContract,
    HasGuardContract
{

}
