<?php
/**
 * Methods for mobile number
 */

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Events\MobileNumberUpdated;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\SmsNotifications\Notifiable\HasSmsNumber;

trait InteractsWithMobileNumbers
{
    use HasSmsNumber;

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function bootInteractsWithMobileNumbers()
    {
        static::deleted(function (HasMobileNumber $user) {
            if (! method_exists($user, 'isForceDeleting') || $user->isForceDeleting()) {
                $user->phone->delete();
            }
        });
    }

    /**
     * A user has one mobile number
     * @return MorphOne
     */
    public function phone(): MorphOne
    {
        return $this->morphOne(MobileVerification::mobileNumberModel(), 'user');
    }

    /**
     * Get full mobile number attribute
     * @return string
     */
    public function getFormattedMobileNumberAttribute(): ?string
    {
        return $this->phone ? $this->phone->formatted_number : null;
    }

    /**
     * Get mobile number country code attribute
     *
     * @return string
     */
    public function getMobileNumberCountryCodeAttribute(): ?string
    {
        return $this->phone ? $this->phone->country_code : null;
    }

    /**
     * Get mobile number attribute
     *
     * @return string
     */
    public function getMobileNumberAttribute(): ?string
    {
        return $this->phone ? $this->phone->number : null;
    }

    /**
     * Get short mobile number attribute
     *
     * @return string
     */
    public function getShortMobileNumberAttribute(): ?string
    {
        return $this->phone ? $this->phone->short_formatted_number : null;
    }

    /**
     * Should verify phone scope
     */
    public function scopeShouldVerifyPhone(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Check if the current user state allows the user to be verified
     *
     * @return bool
     */
    public function getShouldVerifyPhoneAttribute(): bool
    {
        return true;
    }

    /**
     * Needs phone verification scope
     */
    public function scopeNeedsPhoneVerification(Builder $query): Builder
    {
        return $query->shouldVerifyPhone()
                     ->phoneUnverified();
    }

    /**
     * Check if needs phone verification
     * @return bool
     */
    public function getNeedsPhoneVerificationAttribute(): bool
    {
        return  $this->should_verify_phone && ! $this->phone_verified;
    }

    /**
     * Phone unverified scope
     */
    public function scopePhoneUnverified(Builder $query): Builder
    {
        return $query->whereDoesNotHave('phone');
    }

    /**
     * Phone verified scope
     */
    public function scopePhoneVerified(Builder $query): Builder
    {
        return $query->whereHas('phone');
    }

    /**
     * Check if phone verified
     *
     * @return bool
     */
    public function getPhoneVerifiedAttribute(): bool
    {
        return  ! empty($this->phone);
    }

    /**
     * Clear phones
     */
    public function clearPhones()
    {
        $model = MobileVerification::mobileNumberModel();

        $model::where('user_id', $this->getKey())
            ->where('user_type', $this->getMorphClass())
            ->update([
                'user_id' => null,
            ]);
    }

    /**
     * Update the mobile number
     *
     * @param string $number
     * @param string $country_code
     */
    public function updateMobileNumber(string $number, string $country_code = '')
    {
        $model = MobileVerification::mobileNumberModel();

        $phone = $model::blankPhone($number, $country_code, $this->getMorphClass());

        if ($phone) {
            $this->updatePhone($phone);
        }
    }

    /**
     * Update the phone
     *
     * @param MobileNumber $phone
     * @param null $causer
     */
    public function updatePhone($phone, $causer = null)
    {
        $model = MobileVerification::mobileNumberModel();

        if (! ($phone instanceof $model)) {
            $phone = $model::whereId($phone)->first();
        }

        $old_phone = $this->formatted_mobile_number;
        $new_phone = $phone->formatted_number;

        // first clear all current phones
        $this->clearPhones();

        // assign the new phone
        $phone->clearVerificationCode();
        $phone->user()->associate($this);
        $phone->save();

        if ($old_phone != $new_phone) {
            event(new MobileNumberUpdated($old_phone, $new_phone, $this, $causer));
        }
    }

    /**
     * Find mobile number without using country code.
     * By default would assume it's using default country code,
     * unless the number starts with a plus +.
     *
     * @param $query
     * @param $mobile_no
     * @return mixed
     */
    public function scopeHasShortMobileNumber(Builder $query, string $mobile_no): Builder
    {
        return $query->whereHas('phone', function ($query) use ($mobile_no) {
            $query->hasNumber($mobile_no);
        });
    }

    /**
     * Has mobile number scope
     *
     * @param $query
     * @param $mobile_no
     * @param null $country_code
     * @return mixed
     */
    public function scopeHasMobileNumber(Builder $query, string $mobile_no, ?string $country_code = null): Builder
    {
        if (empty($country_code)) {
            $country_code = MobileVerification::defaultCountryCode();
        }

        return $query->whereHas('phone', function ($query) use ($mobile_no, $country_code) {
            $query->where('number', $mobile_no)
                  ->where('country_code', $country_code);
        });
    }

    /**
     * Has phone scope
     * @param $query
     * @param $search
     * @return mixed
     */
    public function scopeHasPhone(Builder $query, string $search): Builder
    {
        return $query->whereHas('phone', function ($query) use ($search) {
            $query->search($search);
        });
    }

    /**
     * The number to send SMSs to
     *
     * @return string
     */
    public function routeNotificationForSms(): string|array|null
    {
        return optional($this->phone)->routeNotificationForSms();
    }

    /**
     * Get email verification url prefix
     *
     * @return string
     */
    public function phoneVerificationUrlPrefix(): string
    {
        return $this->url_prefix;
    }

}
