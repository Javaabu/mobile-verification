<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * A user model with a verified mobile number. Use the InteractsWithMobileNumbers trait.
 */
interface HasMobileNumber
{
    public function phone(): MorphOne;

    public function hasVerifiedMobile(): bool;

    /**
     * Make a number the user's verified number, replacing the old one
     */
    public function updatePhone(MobileNumber|int|string $phone, ?Model $causer = null): void;

    public function updateMobileNumber(string $number, ?string $country_code = null, ?Model $causer = null): ?MobileNumber;

    public function removeMobileNumber(?Model $causer = null): void;

    public function routeNotificationForSms(): string|array|null;
}
