<?php

namespace Javaabu\MobileVerification\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Javaabu\MobileVerification\Events\MobileNumberUpdated;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\SmsNotifications\Notifiable\HasSmsNumber;

/**
 * For user models: one verified mobile number (the `phone` relation), the scopes to find users by it,
 * and the SMS route for notifications.
 *
 * @property-read ?MobileNumber $phone
 * @property-read ?string $formatted_mobile_number
 * @property-read ?string $mobile_number
 * @property-read ?string $mobile_number_country_code
 * @property-read ?string $short_mobile_number
 */
trait InteractsWithMobileNumbers
{
    use HasSmsNumber;

    public static function bootInteractsWithMobileNumbers(): void
    {
        static::deleted(function (Model $user) {
            if (method_exists($user, 'isForceDeleting') && ! $user->isForceDeleting()) {
                return;
            }

            $user->phone()->delete();
        });
    }

    public function phone(): MorphOne
    {
        return $this->morphOne(MobileVerification::mobileNumberModel(), 'user');
    }

    protected function formattedMobileNumber(): Attribute
    {
        return Attribute::get(fn () => $this->phone?->formatted_number);
    }

    protected function mobileNumber(): Attribute
    {
        return Attribute::get(fn () => $this->phone?->number);
    }

    protected function mobileNumberCountryCode(): Attribute
    {
        return Attribute::get(fn () => $this->phone?->country_code);
    }

    protected function shortMobileNumber(): Attribute
    {
        return Attribute::get(fn () => $this->phone?->short_formatted_number);
    }

    protected function phoneVerified(): Attribute
    {
        return Attribute::get(fn () => $this->hasVerifiedMobile());
    }

    protected function needsPhoneVerification(): Attribute
    {
        return Attribute::get(fn () => $this->shouldVerifyPhone() && ! $this->hasVerifiedMobile());
    }

    /**
     * Whether this user must have a verified number. Override for users who don't need one.
     */
    public function shouldVerifyPhone(): bool
    {
        return true;
    }

    public function hasVerifiedMobile(): bool
    {
        return $this->phone !== null;
    }

    public function scopePhoneVerified(Builder $query): Builder
    {
        return $query->whereHas('phone');
    }

    public function scopePhoneUnverified(Builder $query): Builder
    {
        return $query->whereDoesntHave('phone');
    }

    /**
     * Users with a number, without the country code for the default one (`7771234`) or with it (`+9607771234`)
     */
    public function scopeHasShortMobileNumber(Builder $query, string $mobile_number): Builder
    {
        return $query->whereHas('phone', fn (Builder $query) => $query->hasNumber($mobile_number));
    }

    public function scopeHasMobileNumber(Builder $query, string $number, ?string $country_code = null): Builder
    {
        return $query->whereHas('phone', fn (Builder $query) => $query
            ->where('number', MobileVerification::normalizeNumber($number))
            ->where('country_code', MobileVerification::normalizeCountryCode($country_code)));
    }

    public function scopeHasPhone(Builder $query, string $search): Builder
    {
        return $query->whereHas('phone', fn (Builder $query) => $query->search($search));
    }

    /**
     * Find the user with a verified number
     */
    public static function findByMobileNumber(string $number, ?string $country_code = null): ?static
    {
        return static::query()->hasMobileNumber($number, $country_code)->first();
    }

    public function updatePhone(MobileNumber|int|string $phone, ?Model $causer = null): void
    {
        if (! $phone instanceof MobileNumber) {
            $phone = MobileVerification::mobileNumberModel()::query()->findOrFail($phone);
        }

        $old_number = $this->formatted_mobile_number;

        $this->clearPhones($phone);

        $phone->clearVerificationCode();
        $phone->user_type = $this->getMorphClass();
        $phone->user()->associate($this);
        $phone->save();

        $this->unsetRelation('phone');

        if ($old_number !== $phone->formatted_number) {
            MobileNumberUpdated::dispatch($old_number, $phone->formatted_number, $this, $causer);
        }
    }

    /**
     * Give the user a number without a verification code, e.g. from an admin page.
     * Returns null when the number belongs to another user of the same kind.
     */
    public function updateMobileNumber(string $number, ?string $country_code = null, ?Model $causer = null): ?MobileNumber
    {
        $phone = MobileVerification::findOrCreate($this, $country_code, $number);

        if ($phone->hasOwner() && ! $phone->user()->is($this)) {
            return null;
        }

        $this->updatePhone($phone, $causer);

        return $phone;
    }

    public function removeMobileNumber(?Model $causer = null): void
    {
        $old_number = $this->formatted_mobile_number;

        if (! $old_number) {
            return;
        }

        $this->clearPhones();
        $this->unsetRelation('phone');

        MobileNumberUpdated::dispatch($old_number, null, $this, $causer);
    }

    /**
     * Release the user's numbers, keeping one if given
     */
    public function clearPhones(?MobileNumber $except = null): void
    {
        MobileVerification::mobileNumberModel()::query()
            ->where('user_id', $this->getKey())
            ->where('user_type', $this->getMorphClass())
            ->when($except?->exists, fn (Builder $query) => $query->whereKeyNot($except->getKey()))
            ->update(['user_id' => null]);
    }

    public function routeNotificationForSms(): string|array|null
    {
        return $this->phone?->routeNotificationForSms();
    }
}
