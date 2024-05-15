<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\StatefulGuard;
use Javaabu\Auth\Contracts\RegisterContract;

interface RegisterWithMobileNumberContract extends
    SendVerificationCodeContract,
    VerifyVerificationCodeContract,
    HasSessionMobileNumberContract,
    HasGuardContract,
    RegisterContract
{

    public function getRegisterFieldsValidationRules(Request $request): array;

    public function createUser(array $data): HasMobileNumber;

    public function getRegistrationVerificationCodeValidationRules(Request $request): array;

}
