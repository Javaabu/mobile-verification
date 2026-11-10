<?php

namespace Javaabu\MobileVerification\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\Auth\Contracts\TwoFactorAuthenticatable;
use Javaabu\Auth\Support\AccountState;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\MobileVerification;

/**
 * For API clients, which have no session: checks the number, its code, the account and
 * (unless `skip_two_factor`) the two-factor code, and returns the user
 */
class AuthenticateMobileNumber
{
    public function __construct(
        protected VerifyVerificationCode $verify,
        protected StartAuthenticatedSession $start_session,
        protected TwoFactorProvider $two_factor,
    ) {}

    /**
     * @param  array{
     *     number: string,
     *     country_code?: string|null,
     *     verification_code: string,
     *     verification_code_id?: string|null,
     *     code?: string|null,
     *     recovery_code?: string|null
     * }  $input
     *
     * @throws ValidationException
     */
    public function __invoke(UserType $type, array $input): Model
    {
        $mobile_number = MobileVerification::find($type, $input['country_code'] ?? null, $input['number']);
        $user = MobileVerification::userFor($type, $mobile_number);

        if (! $user instanceof Model) {
            throw ValidationException::withMessages(['number' => __('There is no account with this mobile number.')]);
        }

        ($this->verify)($mobile_number, $input['verification_code'], $input['verification_code_id'] ?? null);

        $inactive = AccountState::inactiveMessage($user);

        if ($inactive !== null) {
            throw ValidationException::withMessages(['number' => $inactive]);
        }

        if (! MobileVerification::skipsTwoFactor() && $this->start_session->needsTwoFactorChallenge($type, $user)) {
            $this->checkTwoFactor($user, $input['code'] ?? null, $input['recovery_code'] ?? null);
        }

        return $user;
    }

    protected function checkTwoFactor(TwoFactorAuthenticatable $user, ?string $code, ?string $recovery_code): void
    {
        if (filled($recovery_code)) {
            foreach ($user->recoveryCodes() as $stored) {
                if (hash_equals($stored, (string) $recovery_code)) {
                    $user->replaceRecoveryCode($stored);

                    return;
                }
            }

            throw ValidationException::withMessages(['recovery_code' => __('The provided two-factor recovery code was invalid.')]);
        }

        if (blank($code)) {
            throw ValidationException::withMessages(['code' => __('Enter the code from your authenticator app.')]);
        }

        if (! $this->two_factor->verify((string) $user->twoFactorSecret(), (string) $code)) {
            throw ValidationException::withMessages(['code' => __('The provided two-factor authentication code was invalid.')]);
        }
    }
}
