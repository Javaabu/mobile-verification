<?php

namespace Javaabu\MobileVerification\Actions;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * Saves the registration form's values on a new user of the type. The user table needs
 * a nullable `password` (and `email`, unless the form requires it): see the installation docs.
 */
class CreateUserFromMobileNumber implements CreatesUsersFromMobileNumbers
{
    public function create(UserType $type, array $input, MobileNumber $mobile_number): Model
    {
        $user = $type->newModel();

        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = mb_strtolower(trim($input['email']));
        }

        $user->forceFill($input)->save();

        return $user;
    }
}
