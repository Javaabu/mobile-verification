<?php

namespace Javaabu\MobileVerification\Contracts;

interface UpdateMobileNumberContract extends
    SendVerificationCodeContract,
    VerifyVerificationCodeContract,
    HasSessionMobileNumberContract
{

}
