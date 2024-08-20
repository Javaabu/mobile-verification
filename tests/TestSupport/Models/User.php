<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Models;

use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\Auth\User as Authenticatable;
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

    public function shouldDeletePreservingMedia(): bool
    {
        return true;
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

    public function getAdminUrlAttribute(): string
    {
        // TODO: Implement getAdminUrlAttribute() method.
    }

    public function passwordUpdateUrl()
    {
        // TODO: Implement passwordUpdateUrl() method.
    }

    public function guardName(): string
    {
        return 'web';
    }

    public function homeUrl()
    {
        // TODO: Implement homeUrl() method.
    }

    public function loginUrl()
    {
        // TODO: Implement loginUrl() method.
    }

    public function getRouteForPasswordReset()
    {
        // TODO: Implement getRouteForPasswordReset() method.
    }

    public function getRouteForEmailVerification()
    {
        // TODO: Implement getRouteForEmailVerification() method.
    }

    public function inactiveNoticeUrl()
    {
        // TODO: Implement inactiveNoticeUrl() method.
    }
}
