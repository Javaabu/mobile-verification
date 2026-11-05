<?php

namespace Javaabu\MobileVerification\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes;
use Javaabu\MobileVerification\Database\Factories\MobileNumberFactory;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\SmsNotifications\Notifiable\HasSmsNumber;
use Javaabu\SmsNotifications\Notifiable\SmsNotifiable;

/**
 * A mobile number, owned by a user once it's verified. It holds the current verification code
 * (hashed), when it was sent, and the wrong attempts against it.
 *
 * @property int $id
 * @property string $number
 * @property string $country_code
 * @property string $user_type
 * @property int|string|null $user_id
 * @property int $attempts
 * @property ?string $verification_code
 * @property ?CarbonInterface $verification_code_created_at
 * @property ?string $verification_code_id
 * @property-read string $formatted_number
 * @property-read string $short_formatted_number
 * @property-read string $prefix
 * @property-read ?Model $user
 */
class MobileNumber extends Model implements SmsNotifiable
{
    use HasFactory;
    use HasSmsNumber;
    use Notifiable;

    protected $fillable = [
        'number',
        'country_code',
        'user_type',
    ];

    protected $hidden = [
        'verification_code',
    ];

    protected $appends = [
        'formatted_number',
    ];

    public function getConnectionName(): ?string
    {
        return MobileVerification::config('database_connection') ?: parent::getConnectionName();
    }

    public function getTable(): string
    {
        return MobileVerification::config('table_name') ?: parent::getTable();
    }

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'verification_code_created_at' => 'datetime',
        ];
    }

    protected static function newFactory(): MobileNumberFactory
    {
        return MobileNumberFactory::new();
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * Numbers containing the search, with the prefix and country code: `+9607771234`
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = str_replace(['%', '_'], ['\%', '\_'], ltrim($search, MobileVerification::numberPrefix()));

        return $query->where(function (Builder $query) use ($search) {
            $query->where('number', 'like', "%{$search}%")
                ->orWhereRaw($this->concatCountryCodeAndNumber() . ' like ?', ["%{$search}%"]);
        });
    }

    /**
     * A number written the way users write it: with the prefix and country code (`+9607771234`),
     * or without them for the default country code (`7771234`)
     */
    public function scopeHasNumber(Builder $query, string $search): Builder
    {
        $prefix = MobileVerification::numberPrefix();

        if ($prefix !== '' && str_starts_with($search, $prefix)) {
            return $query->whereRaw($this->concatCountryCodeAndNumber() . ' = ?', [Str::after($search, $prefix)]);
        }

        return $query->where('number', MobileVerification::normalizeNumber($search))
            ->where('country_code', MobileVerification::defaultCountryCode());
    }

    public function scopeHasPhoneNumber(Builder $query, string $country_code, string $number, string $user_type): Builder
    {
        return $query->where('country_code', MobileVerification::normalizeCountryCode($country_code))
            ->where('number', MobileVerification::normalizeNumber($number))
            ->where('user_type', $user_type);
    }

    public function scopeHasPhoneNumberWithOwner(Builder $query, string $country_code, string $number, string $user_type): Builder
    {
        return $query->hasPhoneNumber($country_code, $number, $user_type)->whereNotNull('user_id');
    }

    public function scopeHasPhoneNumberWithoutOwner(Builder $query, string $country_code, string $number, string $user_type): Builder
    {
        return $query->hasPhoneNumber($country_code, $number, $user_type)->whereNull('user_id');
    }

    protected function number(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => MobileVerification::normalizeNumber($value),
        );
    }

    protected function countryCode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => MobileVerification::normalizeCountryCode($value),
        );
    }

    /**
     * Codes are stored hashed
     */
    protected function verificationCode(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Hash::make($value) : null,
        );
    }

    protected function prefix(): Attribute
    {
        return Attribute::get(fn () => $this->country_code ? MobileVerification::numberPrefix() . $this->country_code : '');
    }

    protected function formattedNumber(): Attribute
    {
        return Attribute::get(fn () => $this->prefix . $this->number);
    }

    /**
     * The number without the country code when it has the default one
     */
    protected function shortFormattedNumber(): Attribute
    {
        return Attribute::get(fn () => $this->country_code === MobileVerification::defaultCountryCode()
            ? (string) $this->number
            : $this->formatted_number);
    }

    public function hasOwner(): bool
    {
        return ! empty($this->user_id);
    }

    /**
     * Generate a new code, save it (hashed) and return it, to send to the number
     */
    public function generateVerificationCode(): string
    {
        $code = app(MobileVerification::config('code_generator', GeneratesVerificationCodes::class))->generate();

        $this->attempts = 0;
        $this->verification_code = $code;
        $this->verification_code_created_at = now();
        $this->verification_code_id = (string) Str::uuid();
        $this->save();

        return $code;
    }

    public function hasVerificationCode(): bool
    {
        return ! empty($this->getAttributes()['verification_code'] ?? null);
    }

    /**
     * Check a code. A wrong one counts as an attempt.
     */
    public function checkVerificationCode(?string $code): bool
    {
        $code = trim((string) $code);

        if ($code !== '' && $this->hasVerificationCode() && Hash::check($code, $this->getAttributes()['verification_code'])) {
            return true;
        }

        $this->attempts++;
        $this->save();

        return false;
    }

    /**
     * Forget the code and the attempts, without saving
     */
    public function clearVerificationCode(): static
    {
        $this->attempts = 0;
        $this->verification_code = null;
        $this->verification_code_created_at = null;
        $this->verification_code_id = null;

        return $this;
    }

    /**
     * Forget the code and the attempts, unlocking the number
     */
    public function resetAttempts(): void
    {
        $this->clearVerificationCode()->save();
    }

    /** Minutes a code stays valid */
    public function codeValidity(): int
    {
        return (int) MobileVerification::config('verification_code_validity', 10);
    }

    public function verificationCodeExpiresAt(): ?CarbonInterface
    {
        return $this->verification_code_created_at?->copy()->addMinutes($this->codeValidity());
    }

    public function isVerificationCodeExpired(): bool
    {
        if (! $this->hasVerificationCode()) {
            return true;
        }

        $expires_at = $this->verificationCodeExpiresAt();

        return ! $expires_at || $expires_at->lessThanOrEqualTo(now());
    }

    /** Seconds until the code expires */
    public function verificationCodeExpiresIn(): int
    {
        return $this->isVerificationCodeExpired()
            ? 0
            : $this->secondsUntil($this->verificationCodeExpiresAt());
    }

    public function wasSentRecently(): bool
    {
        return $this->resendVerificationCodeIn() > 0;
    }

    /** Seconds before another code can be sent */
    public function resendVerificationCodeIn(): int
    {
        if (! $this->verification_code_created_at) {
            return 0;
        }

        return $this->secondsUntil(
            $this->verification_code_created_at->copy()->addSeconds((int) MobileVerification::config('resend_interval', 30)),
        );
    }

    /**
     * Locked after too many wrong codes, until attempt_expiry minutes after the code was sent
     */
    public function isLocked(): bool
    {
        return $this->lockExpiresIn() > 0;
    }

    public function hasTooManyAttempts(): bool
    {
        return $this->attempts >= (int) MobileVerification::config('max_attempts', 5);
    }

    public function lockExpiresAt(): ?CarbonInterface
    {
        if (! $this->hasTooManyAttempts()) {
            return null;
        }

        return ($this->verification_code_created_at ?? $this->updated_at ?? now())
            ->copy()
            ->addMinutes((int) MobileVerification::config('attempt_expiry', 30));
    }

    /** Seconds until the number unlocks */
    public function lockExpiresIn(): int
    {
        $expires_at = $this->lockExpiresAt();

        return $expires_at ? $this->secondsUntil($expires_at) : 0;
    }

    public function remainingAttempts(): int
    {
        return max(0, (int) MobileVerification::config('max_attempts', 5) - $this->attempts);
    }

    public function canRequestCode(): bool
    {
        return ! $this->isLocked() && ! $this->wasSentRecently();
    }

    public function isSame(MobileNumber $other): bool
    {
        return $this->country_code === $other->country_code
            && $this->number === $other->number;
    }

    public function routeNotificationForSms(): string|array|null
    {
        return $this->formatted_number;
    }

    /**
     * What JSON responses say about the code
     *
     * @return array<string, mixed>
     */
    public function verificationCodeResponseData(): array
    {
        return [
            'number' => $this->number,
            'country_code' => $this->country_code,
            'formatted_number' => $this->formatted_number,
            'verification_code_id' => $this->verification_code_id,
            'is_registered' => $this->hasOwner(),
            'expires_at' => $this->verificationCodeExpiresAt()?->toIso8601String(),
            'expires_in' => $this->verificationCodeExpiresIn(),
            'resend_interval' => (int) MobileVerification::config('resend_interval', 30),
            'resend_in' => $this->resendVerificationCodeIn(),
            'attempts' => $this->attempts,
            'remaining_attempts' => $this->remainingAttempts(),
            'is_locked' => $this->isLocked(),
            'unlocks_in' => $this->lockExpiresIn(),
        ];
    }

    protected function secondsUntil(CarbonInterface $time): int
    {
        return max(0, (int) ceil(now()->diffInSeconds($time, false)));
    }

    protected function concatCountryCodeAndNumber(): string
    {
        return $this->getConnection()->getDriverName() === 'sqlite'
            ? '(country_code || number)'
            : 'CONCAT(country_code, number)';
    }
}
