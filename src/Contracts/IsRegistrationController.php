<?php

namespace Javaabu\MobileVerification\Contracts;

interface IsRegistrationController
{
    public function registerUser(array $data): HasMobileNumber;

    public function getUserDataValidationRules(array $request_data): array;
}
