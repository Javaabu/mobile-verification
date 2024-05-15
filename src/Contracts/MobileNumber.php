<?php

namespace Javaabu\MobileVerification\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Javaabu\SmsNotifications\Notifiable\SmsNotifiable;

interface MobileNumber extends SmsNotifiable
{
    public function user(): MorphTo;

    public function scopeSearch(Builder $query, string $search): Builder;

    public function scopeHasNumber(Builder $query, string $search): Builder;

    public function randomVerificationCode(): string;

    public function generateVerificationCode(): string;

    public function clearVerificationCode(): void;

    public function isSame(self $other): bool;

    public function getShortFormattedNumberAttribute(): string;

    public function getFormattedNumberAttribute(): string;

    public function getPrefixAttribute(): string;

    public function verifyVerificationCode($verification_code): bool;

    public function getAttemptsExpiryAttribute(): int;

    public function getAttemptsExpiryAtAttribute(): Carbon;

    public function getCanRequestCodeAttribute(): bool;

    public function getVerificationCodeInitiatedTime(): Carbon;

    public function getIsLockedAttribute(): bool;

    public function getVerificationCodeExpiresInAttribute(): int;

    public function getVerificationCodeExpiryAttribute(): int;

    public function getVerificationCodeExpiresAtAttribute(): Carbon;

    public function getIsVerificationCodeExpiredAttribute(): bool;

    public function getWasSentRecentlyAttribute(): bool;

    public function verificationCodeResponseData(): array;

    public function getResendVerificationCodeInAttribute(): int;

    public static function blankPhone(string $number, string $country_code = '', string $user_type = 'user'): ?self;

    public static function getUserByMobileNumber(string $number, string $country_code = '', string $user_type = 'user'): ?HasMobileNumber;
}
