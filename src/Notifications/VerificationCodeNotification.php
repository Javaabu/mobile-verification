<?php

namespace Javaabu\MobileVerification\Notifications;

use Illuminate\Notifications\Notification;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\SmsNotifications\Notifications\SendsSms;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

/**
 * The SMS with a verification code, sent through javaabu/sms-notifications in the locale of the request
 */
class VerificationCodeNotification extends Notification implements SmsNotification
{
    use SendsSms;

    public function __construct(
        public string $verification_code,
        public Purposes $purpose = Purposes::LOGIN,
        public ?string $name = null,
    ) {
        $this->locale(app()->getLocale());
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return [$this->getSmsChannel()];
    }

    public function toSms($notifiable): string
    {
        return __('Your :app verification code is :code. It expires in :minutes minutes. Do not share it with anyone.', [
            'app' => config('app.name'),
            'code' => $this->verification_code,
            'minutes' => (int) MobileVerification::config('verification_code_validity', 10),
        ]);
    }
}
