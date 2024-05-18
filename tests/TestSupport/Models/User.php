<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Models;

use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Traits\InteractsWithMobileNumbers;
use Javaabu\MobileVerification\Tests\TestSupport\Factories\UserFactory;
use Javaabu\MobileVerification\Contracts\ShouldHaveVerifiedMobileNumber;

class User extends Authenticatable implements ShouldHaveVerifiedMobileNumber
{
    use HasFactory;
    use InteractsWithMobileNumbers;
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

    public function findMobileGrantUser($oauth_user, $provider): ?HasMobileNumber
    {
        $number = $http_response_header->getNumber();
        $user = User::whereHas('phone', function ($query) use ($number) {
            $query->where('number', $number);
        })->first();

        return $user;
    }

    public function redirectToMobileVerificationUrl(): RedirectResponse
    {
        return to_route('mobile-verifications.login.create');
    }
}
