<?php

namespace Javaabu\MobileVerification\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Javaabu\MobileVerification\Contracts\MobileNumber as MobileNumberContract;
use Javaabu\MobileVerification\Factories\MobileNumberFactory;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\SmsNotifications\Notifiable\HasSmsNumber;

class MobileNumber extends Model implements MobileNumberContract
{
    use Notifiable;
    use HasSmsNumber;
    use HasFactory;

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
        'token_created_at' => 'datetime',
    ];

    /**
     * Hidden
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Appends
     *
     * @var array
     */
    protected $appends = [
        'formatted_number',
        'token_expires_in',
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
     * Convert dates to Carbon
     * @param $date
     */
    public function setTokenCreatedAtAttribute($date)
    {
        $this->attributes['token_created_at'] = empty($date) ? null : Carbon::parse($date);
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

    public function scopeHasPhoneNumber($query, string $country_code, string $number, string $user_type): void
    {
        $query->where('country_code', $country_code)
              ->where('number', $number)
              ->where('user_type', $user_type)
              ->where('user_id', '!=', null);
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
     * Hash the token before saving
     * @param $value
     */
    public function setTokenAttribute($value)
    {
        $this->attributes['token'] = $value ? Hash::make($value) : null;
    }

    /**
     * Generate the token
     */
    public function randomToken(): string
    {
        return str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
    }

    /**
     * Clear the token fields
     */
    public function clearToken(): void
    {
        $this->attempts = 0;
        $this->token = null;
        $this->token_created_at = null;
    }

    /**
     * Generate the token and save
     */
    public function generateToken(): string
    {
        $token = $this->randomToken();

        $this->attempts = 0;
        $this->token = $token;
        $this->token_created_at = Carbon::now();
        $this->save();

        return $token;
    }

    /**
     * Check if was sent recently
     * @return bool
     */
    public function getWasSentRecentlyAttribute(): bool
    {
        return $this->token_created_at && $this->token_created_at->diffInSeconds() < self::config('resend_interval');
    }

    /**
     * Check if token is expired
     */
    public function getIsTokenExpiredAttribute(): bool
    {
        return ! $this->token ||
            ($this->token_created_at &&
                $this->token_created_at->diffInMinutes() >= $this->token_expires_in);
    }

    /**
     * Get token expires at time
     */
    public function getTokenExpiresAtAttribute(): Carbon
    {
        return $this->getTokenInitiatedTime()->addMinutes($this->token_expires_in);
    }

    /**
     * Get token expiry in seconds
     */
    public function getTokenExpiryAttribute(): int
    {
        return $this->is_token_expired ? 0 : $this->token_expires_at->diffInSeconds();
    }

    /**
     * Get token expires in attribute
     */
    public function getTokenExpiresInAttribute(): int
    {
        return MobileVerification::config('token_validity');
    }

    /**
     * Check if number is locked
     */
    public function getIsLockedAttribute(): bool
    {
        return $this->attempts >= MobileVerification::config('max_attempts');
    }

    /**
     * Get the token created at time or the current time
     * @return Carbon
     */
    public function getTokenInitiatedTime(): Carbon
    {
        return $this->token_created_at ?: Carbon::now();
    }

    /**
     * Check if can request for new code
     */
    public function getCanRequestCodeAttribute(): bool
    {
        return ! $this->is_locked ||
            $this->getTokenInitiatedTime()->diffInMinutes() >= MobileVerification::config('attempt_expiry');
    }

    /**
     * Attempts expiry at
     */
    public function getAttemptsExpiryAtAttribute(): Carbon
    {
        return ! $this->is_locked ? Carbon::now() :
            $this->getTokenInitiatedTime()->addMinutes(MobileVerification::config('attempt_expiry'));
    }

    /**
     * Get attempts expiry minutes
     */
    public function getAttemptsExpiryAttribute(): int
    {
        return $this->can_request_code ? 0 : $this->attempts_expiry_at->diffInMinutes();
    }

    /**
     * Verify token
     */
    public function verifyToken($token): bool
    {
        if ($token && $this->token && Hash::check($token, $this->token)) {
            $this->attempts = 0;
            $this->token = null;
            $this->token_created_at = null;
            $this->save();

            return true;
        }

        $this->attempts++;
        $this->save();

        return false;
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
}
