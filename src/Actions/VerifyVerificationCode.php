<?php

namespace Javaabu\MobileVerification\Actions;

use Illuminate\Validation\ValidationException;
use Javaabu\MobileVerification\Events\MobileNumberLocked;
use Javaabu\MobileVerification\Events\VerificationCodeFailed;
use Javaabu\MobileVerification\Models\MobileNumber;

/**
 * Checks a code against a number. A wrong code counts as an attempt; too many lock the number.
 * The right code is used up, so it can't be entered twice.
 */
class VerifyVerificationCode
{
    /**
     * @param  string|null  $verification_code_id  the id given when the code was sent; null skips the check
     *
     * @throws ValidationException
     */
    public function __invoke(
        MobileNumber $mobile_number,
        ?string $verification_code,
        ?string $verification_code_id = null,
        string $error_key = 'verification_code',
    ): void {
        if ($mobile_number->isLocked()) {
            $this->fail($mobile_number, 'locked', SendVerificationCode::lockedMessage($mobile_number), $error_key, 429);
        }

        if ($verification_code_id !== null && ! hash_equals((string) $mobile_number->verification_code_id, $verification_code_id)) {
            $this->fail($mobile_number, 'mismatch', __('This code is no longer valid. Ask for a new code.'), $error_key);
        }

        if ($mobile_number->isVerificationCodeExpired()) {
            $this->fail($mobile_number, 'expired', __('The code has expired. Ask for a new code.'), $error_key);
        }

        if (! $mobile_number->checkVerificationCode($verification_code)) {
            if ($mobile_number->isLocked()) {
                MobileNumberLocked::dispatch($mobile_number);

                $this->fail($mobile_number, 'locked', SendVerificationCode::lockedMessage($mobile_number), $error_key, 429);
            }

            $this->fail($mobile_number, 'incorrect', trans_choice('The code is incorrect. You have :count attempt left.|The code is incorrect. You have :count attempts left.', $mobile_number->remainingAttempts()), $error_key);
        }

        $mobile_number->resetAttempts();
    }

    protected function fail(MobileNumber $mobile_number, string $reason, string $message, string $error_key, int $status = 422): never
    {
        VerificationCodeFailed::dispatch($mobile_number, $reason);

        throw ValidationException::withMessages([$error_key => $message])->status($status);
    }
}
