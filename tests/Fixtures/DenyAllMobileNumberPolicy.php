<?php

namespace Javaabu\MobileVerification\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Policies\MobileNumberPolicy;

class DenyAllMobileNumberPolicy extends MobileNumberPolicy
{
    public function update(Model $user, UserType $type, ?Model $account = null): bool
    {
        return false;
    }
}
