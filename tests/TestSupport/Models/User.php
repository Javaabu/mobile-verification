<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Factories\UserFactory;
use Javaabu\MobileVerification\Traits\InteractsWithMobileNumbers;

class User extends Authenticatable implements HasMobileNumber
{
    use InteractsWithMobileNumbers;
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory()
    {
        return new UserFactory();
    }

    public function phoneVerificationRedirectUrl(): string
    {
        return '';
    }

    public function phoneVerificationUrl(): string
    {
        return '';
    }
}
