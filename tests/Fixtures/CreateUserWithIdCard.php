<?php

namespace Javaabu\MobileVerification\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers;
use Javaabu\MobileVerification\Models\MobileNumber;

class CreateUserWithIdCard implements CreatesUsersFromMobileNumbers
{
    public function create(UserType $type, array $input, MobileNumber $mobile_number): Model
    {
        $user = $type->newModel();

        $user->forceFill(['name' => "{$input['name']} ({$input['id_card']})"])->save();

        return $user;
    }
}
