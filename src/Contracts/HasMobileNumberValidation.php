<?php

namespace Javaabu\MobileVerification\Contracts;

interface HasMobileNumberValidation
{
    /*
     * Whether the mobile number must be a registered mobile number.
     * If the mobile number must be a registered mobile number, then the user will be redirected back with an error if the number is already registered.
     * If the mobile number must not be a registered mobile number, then the user will be redirected back with an error if the number is not registered.
     * This method should be overridden in the controller to return true if the mobile number must be a registered mobile number.
     * This method can be used to handle OTP requests for both registration and login.
     * */
    public function mustBeARegisteredMobileNumber(array $request_data): bool;

}
