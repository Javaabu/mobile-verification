<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * Creates the user at the end of the mobile registration. The package attaches the verified
 * number and logs the user in afterwards.
 */
interface CreatesUsersFromMobileNumbers
{
    /**
     * @param  array<string, mixed>  $input  the values the registration form validated
     */
    public function create(UserType $type, array $input, MobileNumber $mobile_number): Model;
}
