<?php
/**
 * The contract for phone verifiable
 */

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Javaabu\SmsNotifications\Notifiable\SmsNotifiable;

interface HasMobileNumber extends SmsNotifiable
{
    /**
     * A user has one mobile number
     *
     * @return MorphOne
     */
    public function phone(): MorphOne;

    /**
     * Get full mobile number attribute
     *
     * @return string
     */
    public function getFormattedMobileNumberAttribute(): ?string;

    /**
     * Get mobile number country code attribute
     *
     * @return string
     */
    public function getMobileNumberCountryCodeAttribute(): ?string;

    /**
     * Get mobile number attribute
     *
     * @return string
     */
    public function getMobileNumberAttribute(): ?string;

    /**
     * Get short mobile number attribute
     *
     * @return string
     */
    public function getShortMobileNumberAttribute(): ?string;

    /**
     * Should verify phone scope
     *
     * @param $query
     * @return
     */
    public function scopeShouldVerifyPhone(Builder $query): Builder;

    /**
     * Check if the current user state allows the user to be verified
     *
     * @return bool
     */
    public function getShouldVerifyPhoneAttribute(): bool;

    /**
     * Needs phone verification scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeNeedsPhoneVerification(Builder $query): Builder;

    /**
     * Check if needs phone verification
     *
     * @return bool
     */
    public function getNeedsPhoneVerificationAttribute(): bool;

    /**
     * Phone unverified scope
     *
     * @param $query
     * @return
     */
    public function scopePhoneUnverified(Builder $query): Builder;

    /**
     * Phone verified scope
     *
     * @param $query
     * @return mixed
     */
    public function scopePhoneVerified(Builder $query): Builder;

    /**
     * Check if phone verified
     *
     * @return bool
     */
    public function getPhoneVerifiedAttribute(): bool;

    public function hasVerifiedMobile(): bool;

    /**
     * Clear phones
     */
    public function clearPhones();

    /**
     * Update the mobile number
     *
     * @param string $number
     * @param string $country_code
     */
    public function updateMobileNumber(string $number, string $country_code = '');

    /**
     * Update the phone
     *
     * @param MobileNumber $phone
     * @param null $causer
     */
    public function updatePhone($phone, $causer = null);

    /**
     * Find mobile number without using country code.
     * By default would assume it's using default country code,
     * unless the number starts with a plus +.
     *
     * @param $query
     * @param $mobile_no
     * @return mixed
     */
    public function scopeHasShortMobileNumber(Builder $query, string $mobile_no): Builder;

    /**
     * Has mobile number scope
     *
     * @param $query
     * @param $mobile_no
     * @param null $country_code
     * @return mixed
     */
    public function scopeHasMobileNumber(Builder $query, string $mobile_no, ?string $country_code = null): Builder;

    /**
     * Has phone scope
     *
     * @param $query
     * @param $search
     * @return mixed
     */
    public function scopeHasPhone(Builder $query, string $search): Builder;
}
