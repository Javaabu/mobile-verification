<?php

namespace Javaabu\MobileVerification\Contracts;

interface LoginWithMobileNumberContract extends
    SendVerificationCodeContract,
    VerifyVerificationCodeContract,
    HasSessionMobileNumberContract,
    HasGuardContract
{
}
