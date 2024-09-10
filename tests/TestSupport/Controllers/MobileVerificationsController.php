<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Traits\HasUserType;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Traits\UpdatesMobileNumber;
use Javaabu\SmsNotifications\Notifications\SmsNotification;
use Javaabu\MobileVerification\Traits\SendsVerificationCode;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\HasRequestMobileNumber;
use Javaabu\MobileVerification\Traits\VerifiesVerificationCode;
use Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract;

class MobileVerificationsController
{
    use SendsVerificationCode;
    use VerifiesVerificationCode;
    use HasUserType;
    use HasRequestMobileNumber;

    public function getUserClass(Request $request): string
    {
        $request->validate([
            'user_type' => 'required|string|in:user'
        ]);

        return match ($request->user_type) {
            'user' => User::class,
        };
    }

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        if (data_get($request_data, 'user_type') === 'user') {
            return true;
        }

        return null;
    }

    public function getVerificationCodeSmsNotification(string $verification_code, MobileNumber $mobile_number): SmsNotification
    {
        $notification_class = config('mobile-verification.notifications.login');
        return new $notification_class($verification_code, $mobile_number?->user?->name);
    }


}
