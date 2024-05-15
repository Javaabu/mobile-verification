<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

trait UpdatesMobileNumber
{
    use SendsVerificationCode;
    use UsesSessionMobileNumber;
    use VerifiesVerificationCode;
    use HasRequestMobileNumber;
    use HasUserType;

    public function getVerificationCodeSmsNotification(string $verification_code, MobileNumber $mobile_number): SmsNotification
    {
        $notification_class = config('mobile-verification.notifications.update');
        return new $notification_class($verification_code, $mobile_number);
    }


    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return false;
    }

    public function getSessionMobileNumberKey(): string
    {
        return 'mobile_to_update';
    }

    public function shouldAddMobileNumberToSession(): bool
    {
        return true;
    }
}