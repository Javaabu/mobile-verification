<?php

namespace Javaabu\MobileVerification\Tests\Models\Sanctum;

use Javaabu\MobileVerification\Tests\Factories\UserFactory;
use Javaabu\MobileVerification\Tests\Models\User as BaseUser;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
    use HasApiTokens;

    protected static function newFactory(): UserFactory
    {
        return new class extends UserFactory
        {
            protected $model = User::class;
        };
    }
}
