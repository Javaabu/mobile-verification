<?php

namespace Javaabu\MobileVerification\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Javaabu\Activitylog\Traits\LogsActivity;
use Javaabu\MobileVerification\MobileVerification;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Javaabu\SmsNotifications\Notifiable\HasSmsNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Factories\MobileNumberFactory;
use Javaabu\MobileVerification\Contracts\MobileNumber as MobileNumberContract;

class MobileNumber extends Model implements MobileNumberContract
{
    use HasFactory;
    use HasSmsNumber;
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'country_code',
        'user_type',
    ];

    /**
     * Dates
     *
     * @var array
     */
    protected $casts = [
        'verification_code_created_at' => 'datetime',
    ];

    /**
     * Hidden
     *
     * @var array
     */
    protected $hidden = [
        'verification_code',
    ];

    /**
     * Appends
     *
     * @var array
     */
    protected $appends = [
        'formatted_number',
        'verification_code_expires_in',
        'resend_verification_code_in',
    ];

    protected static array $logExceptAttributes = [
        'verification_code',
    ];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return new MobileNumberFactory();
    }

    /**
     * A mobile number belongs to a user
     *
     * @return MorphTo
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }


    /**
     * A search scope
     *
     * @param $query
     * @param $search
     * @return
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(DB::raw('CONCAT(\'' . MobileVerification::config('number_prefix') . '\', country_code, number)'), 'like', '%' . $search . '%');
    }

    /**
     * Find mobile number without using country code.
     * By default would assume it's using default country code,
     * unless the number starts with a plus +.
     *
     * @param $query
     * @param $search
     */
    public function scopeHasNumber(Builder $query, string $search): Builder
    {
        $prefix = self::config('number_prefix');

        // check if starts with the country code
        if (Str::startsWith($search, $prefix)) {
            // extract the number without the prefix
            $number = Str::after($search, $prefix);

            return $query->where(DB::raw('CONCAT(country_code, number)'), $number);
        }

        // otherwise check against the default country code
        return $query->where('number', $search)
                     ->where('country_code', MobileVerification::defaultCountryCode());
    }

    public function scopeHasPhoneNumberWithOwner($query, string $country_code, string $number, string $user_type): void
    {
        $query->where('country_code', $country_code)
              ->where('number', $number)
              ->where('user_type', $user_type)
              ->where('user_id', '!=', null);
    }

    public function scopeHasPhoneNumberWithoutOwner($query, string $country_code, string $number, string $user_type): void
    {
        $query->where('country_code', $country_code)
              ->where('number', $number)
              ->where('user_type', $user_type)
              ->whereNull('user_id');
    }

    public function scopeHasPhoneNumber($query, string $country_code, string $number, string $user_type): void
    {
        $query->where('country_code', $country_code)
              ->where('number', $number)
              ->where('user_type', $user_type);
    }


    /**
     * Convert dates to Carbon
     * @param $date
     */
    public function setTokenCreatedAtAttribute($date)
    {
        $this->attributes['verification_code_created_at'] = empty($date) ? null : Carbon::parse($date);
    }

    /**
     * Normalize mobile numbers before saving
     * @param $number
     */
    public function setNumberAttribute($number)
    {
        $this->attributes['number'] = MobileVerification::normalizeNumber($number);
    }

    /**
     * Default to the default country code
     * @param $value
     */
    public function setCountryCodeAttribute($value)
    {
        $this->attributes['country_code'] = $value ?: MobileVerification::defaultCountryCode();
    }

    /**
     * Hash the verification_code before saving
     * @param $value
     */
    public function setVerificationCodeAttribute($value)
    {
        $this->attributes['verification_code'] = $value ? Hash::make($value) : null;
    }

    public function hasUnexpiredVerificationCode(): bool
    {
        return $this->verification_code && (! $this->is_verification_code_expired);
    }

    /**
     * Generate the verification_code
     */
    public function randomVerificationCode(): string
    {
        return str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
    }

    /**
     * Clear the verification_code fields
     */
    public function clearVerificationCode(): void
    {
        $this->attempts = 0;
        $this->verification_code = null;
        $this->verification_code_created_at = null;
        $this->verification_code_id = null;
    }

    /**
     * Generate the verification_code and save
     */
    public function generateVerificationCode(): string
    {
        $verification_code = $this->randomVerificationCode();

        $this->attempts = 0;
        $this->verification_code = $verification_code;
        $this->verification_code_created_at = Carbon::now();
        $this->verification_code_id = Str::uuid();
        $this->save();

        return $verification_code;
    }

    /**
     * Check if was sent recently
     * @return bool
     */
    public function getWasSentRecentlyAttribute(): bool
    {
        return $this->verification_code_created_at && $this->verification_code_created_at->diffInSeconds() < MobileVerification::config('resend_interval');
    }

    public function getResendVerificationCodeInAttribute(): int
    {
        return $this->was_sent_recently
            ? MobileVerification::config('resend_interval') - $this->verification_code_created_at->diffInSeconds()
            : 0;
    }

    /**
     * Check if verification_code is expired
     */
    public function getIsVerificationCodeExpiredAttribute(): bool
    {
        return ! $this->verification_code ||
            ($this->verification_code_created_at &&
                $this->verification_code_created_at->diffInMinutes() >= $this->verification_code_expires_in);
    }

    /**
     * Get verification_code expires at time
     */
    public function getVerificationCodeExpiresAtAttribute(): Carbon
    {
        return $this->getVerificationCodeInitiatedTime()->addMinutes($this->verification_code_expires_in);
    }

    /**
     * Get verification_code expiry in seconds
     */
    public function getVerificationCodeExpiryAttribute(): int
    {
        return $this->is_verification_code_expired ? 0 : now()->diffInSeconds($this->verification_code_expires_at);
    }

    /**
     * Get verification_code expires in attribute
     */
    public function getVerificationCodeExpiresInAttribute(): int
    {
        return MobileVerification::config('verification_code_validity');
    }

    /**
     * Check if number is locked
     */
    public function getIsLockedAttribute(): bool
    {
        return $this->attempts >= MobileVerification::config('max_attempts');
    }

    /**
     * Get the verification_code created at time or the current time
     * @return Carbon
     */
    public function getVerificationCodeInitiatedTime(): Carbon
    {
        return $this->verification_code_created_at ?: Carbon::now();
    }

    /**
     * Check if can request for new code
     */
    public function getCanRequestCodeAttribute(): bool
    {
        return ! $this->is_locked ||
            $this->getVerificationCodeInitiatedTime()->diffInMinutes() >= MobileVerification::config('attempt_expiry');
    }

    /**
     * Attempts expiry at
     */
    public function getAttemptsExpiryAtAttribute(): Carbon
    {
        return ! $this->is_locked ? Carbon::now() :
            $this->getVerificationCodeInitiatedTime()->addMinutes(MobileVerification::config('attempt_expiry'));
    }

    /**
     * Get attempts expiry minutes
     */
    public function getAttemptsExpiryAttribute(): int
    {
        return $this->can_request_code ? 0 : $this->attempts_expiry_at->diffInMinutes();
    }

    /**
     * Verify verification_code
     */
    public function verifyVerificationCode($verification_code, bool $should_reset = false): bool
    {
        if ($verification_code && $this->verification_code && Hash::check($verification_code, $this->verification_code)) {

            if ($should_reset) {
                $this->resetAttempts();
            }

            return true;
        }

        $this->attempts++;
        $this->save();

        return false;
    }

    public function resetAttempts(): void
    {
        $this->attempts = 0;
        $this->verification_code = null;
        $this->verification_code_created_at = null;
        $this->verification_code_id = null;
        $this->save();
    }

    /**
     * Get the prefix
     * @return string
     */
    public function getPrefixAttribute(): string
    {
        return $this->country_code ? MobileVerification::config('number_prefix') . $this->country_code : '';
    }

    /**
     * Get the formatted number
     */
    public function getFormattedNumberAttribute(): string
    {
        return $this->prefix . $this->number;
    }

    /**
     * Get the short formatted number
     */
    public function getShortFormattedNumberAttribute(): string
    {
        return $this->country_code == MobileVerification::defaultCountryCode() ?
            $this->number :
            $this->prefix . $this->number;
    }

    /**
     * The number to send SMSs to
     *
     * @return string
     */
    public function routeNotificationForSms(): array|string|null
    {
        return $this->formatted_number;
    }

    /**
     * Check if is same
     * @param MobileNumber $other
     * @return bool
     */
    public function isSame(\Javaabu\MobileVerification\Contracts\MobileNumber $other): bool
    {
        return $this->country_code == $other->country_code
            && $this->number == $other->number;
    }

    /**
     * Blank number
     * @param $number
     * @param string $country_code
     * @param string $user_type
     * @return MobileNumber|null
     */
    public static function blankPhone(string $number, string $country_code = '', string $user_type = 'user'): ?\Javaabu\MobileVerification\Contracts\MobileNumber
    {
        $country_code = $country_code ?: MobileVerification::defaultCountryCode();
        $number = MobileVerification::normalizeNumber($number);

        $model = MobileVerification::mobileNumberModel();
        $phone = $model::firstOrCreate(compact(
            'number',
            'country_code',
            'user_type'
        ));

        if (! $phone->user_id) {
            return $phone;
        }

        return null;
    }

    public static function getUserByMobileNumber(string $number, string $country_code = '', string $user_type = 'user'): ?HasMobileNumber
    {
        $country_code = $country_code ?: MobileVerification::defaultCountryCode();
        $number = MobileVerification::normalizeNumber($number);

        $model = MobileVerification::mobileNumberModel();
        $phone = $model::query()->where('number', $number)
                       ->where('country_code', $country_code)
                       ->where('user_type', $user_type)
                       ->first();

        return $phone->user;
    }

    public function verificationCodeResponseData(): array
    {
        return [
            'verification_code_id' => $this->verification_code_id,
            'user_type' => $this->user_type,
            'is_registered' => (bool)$this->user_id,
            'expires_at' => $this->verification_code_expires_at,
            'expiry_duration' => $this->verification_code_expiry,
            'expires_in' => $this->verification_code_expires_in,
            'resend_interval' => MobileVerification::config('resend_interval'),
            'resend_in' => $this->resend_verification_code_in,
            'attempts' => $this->attempts,
            'is_locked' => $this->is_locked,
            'attempt_expiry' => $this->attempts_expiry,
        ];
    }
}
