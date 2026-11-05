<?php

namespace Javaabu\MobileVerification\Tests\Models\Passport;

use Javaabu\MobileVerification\Tests\Factories\UserFactory;
use Javaabu\MobileVerification\Tests\Models\User as BaseUser;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends BaseUser implements OAuthenticatable
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
