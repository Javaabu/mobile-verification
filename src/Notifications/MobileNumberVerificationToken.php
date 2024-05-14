<?php

namespace Javaabu\MobileVerification\Notifications;

use Illuminate\Notifications\Notification;
use Javaabu\SmsNotifications\Notifications\SendsSms;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

class MobileNumberVerificationToken extends Notification implements SmsNotification
{
    use SendsSms;

    /**
     * The password reset verification_code.
     */
    public string $verification_code;

    /**
     * The name of the intended user
     */
    public string $name;

    /**
     * Create a new notification instance.
     *
     * @param string $verification_code
     * @param string $name
     */
    public function __construct(string $verification_code, string $name = '')
    {
        $this->verification_code = $verification_code;
        $this->name = $name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [$this->getSmsChannel()];
    }

    /**
     * Get the sms representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function toSms($notifiable): string
    {
        return  "Dear ".($this->name ?: 'User').",\n".
                'Your '.get_setting('app_name').' account verification code is '.$this->verification_code;
    }
}
