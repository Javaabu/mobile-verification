<?php

namespace Javaabu\MobileVerification\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\Events\VerificationCodeSent;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;

/**
 * Generates a code for a number and sends it by SMS, unless the number is locked or got a code
 * too recently. Login codes go to numbers users have verified; registration and update codes go to
 * numbers no other user of the type has.
 */
class SendVerificationCode
{
    /**
     * @param  Model|null  $user  the signed-in user changing their number, for Purposes::UPDATE
     *
     * @throws ValidationException
     */
    public function __invoke(
        UserType $type,
        Purposes $purpose,
        ?string $country_code,
        string $number,
        ?Model $user = null,
        string $error_key = 'number',
    ): MobileNumber {
        $mobile_number = $this->findMobileNumber($type, $purpose, $country_code, $number, $user, $error_key);

        $this->ensureCanSend($mobile_number, $error_key);

        $code = $mobile_number->generateVerificationCode();

        $mobile_number->notify($this->notification($code, $purpose, $mobile_number, $user));

        VerificationCodeSent::dispatch($mobile_number, $purpose);

        return $mobile_number;
    }

    protected function findMobileNumber(
        UserType $type,
        Purposes $purpose,
        ?string $country_code,
        string $number,
        ?Model $user,
        string $error_key,
    ): MobileNumber {
        if ($purpose->needsOwner()) {
            $mobile_number = MobileVerification::find($type, $country_code, $number);

            if (! $mobile_number?->hasOwner() || ! $mobile_number->user) {
                throw ValidationException::withMessages([
                    $error_key => __('There is no account with this mobile number.'),
                ]);
            }

            return $mobile_number;
        }

        $mobile_number = MobileVerification::findOrCreate($type, $country_code, $number);

        if ($mobile_number->hasOwner() && ! ($user && $mobile_number->user()->is($user))) {
            throw ValidationException::withMessages([
                $error_key => __('This mobile number is already registered.'),
            ]);
        }

        if ($user && $mobile_number->user()->is($user)) {
            throw ValidationException::withMessages([
                $error_key => __('This is already your mobile number.'),
            ]);
        }

        return $mobile_number;
    }

    protected function ensureCanSend(MobileNumber $mobile_number, string $error_key): void
    {
        if ($mobile_number->isLocked()) {
            throw ValidationException::withMessages([
                $error_key => static::lockedMessage($mobile_number),
            ])->status(429);
        }

        if ($mobile_number->wasSentRecently()) {
            throw ValidationException::withMessages([
                $error_key => __('A code was sent to this number recently. You can ask for another in :seconds seconds.', [
                    'seconds' => $mobile_number->resendVerificationCodeIn(),
                ]),
            ])->status(429);
        }
    }

    protected function notification(string $code, Purposes $purpose, MobileNumber $mobile_number, ?Model $user): VerificationCodeNotification
    {
        $class = MobileVerification::config('notification', VerificationCodeNotification::class);
        $owner = $user ?? $mobile_number->user;

        return new $class($code, $purpose, $owner?->getAttribute('name'));
    }

    public static function lockedMessage(MobileNumber $mobile_number): string
    {
        return __('This number is locked after too many wrong codes. Try again in :minutes minutes.', [
            'minutes' => max(1, (int) ceil($mobile_number->lockExpiresIn() / 60)),
        ]);
    }
}
