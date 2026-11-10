<?php

namespace Javaabu\MobileVerification\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\MobileVerification\Concerns\InteractsWithMobileNumbers;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Tests\Factories\AdminFactory;
use Javaabu\Permissions\Traits\HasRoles;

class Admin extends BaseUser implements HasMobileNumber
{
    use HasFactory;
    use HasRoles;
    use InteractsWithMobileNumbers;

    protected $table = 'admins';

    protected static function newFactory(): AdminFactory
    {
        return AdminFactory::new();
    }
}
