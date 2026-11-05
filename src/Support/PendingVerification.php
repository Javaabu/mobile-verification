<?php

namespace Javaabu\MobileVerification\Support;

use Illuminate\Http\Request;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * The number a browser is verifying, kept in the session per user type and purpose,
 * so the code page doesn't need the number in the URL
 */
class PendingVerification
{
    public static function key(UserType $type, Purposes $purpose, string $suffix = 'pending'): string
    {
        return "mobile-verification.{$type->name()}.{$purpose->value}.{$suffix}";
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public static function put(Request $request, UserType $type, Purposes $purpose, MobileNumber $mobile_number, array $extra = []): void
    {
        $request->session()->put(static::key($type, $purpose), [
            ...$extra,
            'id' => $mobile_number->getKey(),
            'verification_code_id' => $mobile_number->verification_code_id,
        ]);
    }

    /** @return array<string, mixed>|null */
    public static function get(Request $request, UserType $type, Purposes $purpose): ?array
    {
        $pending = $request->session()->get(static::key($type, $purpose));

        return is_array($pending) && isset($pending['id']) ? $pending : null;
    }

    public static function mobileNumber(Request $request, UserType $type, Purposes $purpose): ?MobileNumber
    {
        $pending = static::get($request, $type, $purpose);

        if (! $pending) {
            return null;
        }

        return MobileVerification::mobileNumberModel()::query()
            ->whereKey($pending['id'])
            ->where('user_type', MobileVerification::morphClassFor($type))
            ->first();
    }

    public static function forget(Request $request, UserType $type, Purposes $purpose): void
    {
        $request->session()->forget(static::key($type, $purpose));
    }

    /**
     * Remember that the browser verified a number, e.g. before the registration details
     */
    public static function markVerified(Request $request, UserType $type, Purposes $purpose, MobileNumber $mobile_number): void
    {
        static::forget($request, $type, $purpose);

        $request->session()->put(static::key($type, $purpose, 'verified'), [
            'id' => $mobile_number->getKey(),
            'verified_at' => now()->getTimestamp(),
        ]);
    }

    /**
     * The number the browser verified, within `verification_code_validity` minutes
     */
    public static function verifiedMobileNumber(Request $request, UserType $type, Purposes $purpose): ?MobileNumber
    {
        $verified = $request->session()->get(static::key($type, $purpose, 'verified'));

        if (! is_array($verified) || ! isset($verified['id'], $verified['verified_at'])) {
            return null;
        }

        $expires_at = (int) $verified['verified_at'] + ((int) MobileVerification::config('verification_code_validity', 10) * 60);

        if ($expires_at <= now()->getTimestamp()) {
            return null;
        }

        return MobileVerification::mobileNumberModel()::query()
            ->whereKey($verified['id'])
            ->where('user_type', MobileVerification::morphClassFor($type))
            ->first();
    }

    public static function forgetVerified(Request $request, UserType $type, Purposes $purpose): void
    {
        $request->session()->forget(static::key($type, $purpose, 'verified'));
    }
}
