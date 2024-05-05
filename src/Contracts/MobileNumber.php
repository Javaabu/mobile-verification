<?php

namespace Javaabu\MobileVerification\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Javaabu\SmsNotifications\Notifiable\SmsNotifiable;

interface MobileNumber extends SmsNotifiable
{
    public static function blankPhone(string $number, string $country_code = '', string $user_type = 'user'): ?self;

    public function user(): MorphTo;

    public function scopeSearch(Builder $query, string $search): Builder;

    public function scopeHasNumber(Builder $query, string $search): Builder;

    public function randomToken(): string;

    public function generateToken(): string;

    public function clearToken(): void;

    public function isSame(self $other): bool;

    public function getShortFormattedNumberAttribute(): string;

    public function getFormattedNumberAttribute(): string;

    public function getPrefixAttribute(): string;

    public function verifyToken($token): bool;

    public function getAttemptsExpiryAttribute(): int;

    public function getAttemptsExpiryAtAttribute(): Carbon;

    public function getCanRequestCodeAttribute(): bool;

    public function getTokenInitiatedTime(): Carbon;

    public function getIsLockedAttribute(): bool;

    public function getTokenExpiresInAttribute(): int;

    public function getTokenExpiryAttribute(): int;

    public function getTokenExpiresAtAttribute(): Carbon;

    public function getIsTokenExpiredAttribute(): bool;

    public function getWasSentRecentlyAttribute(): bool;
}
