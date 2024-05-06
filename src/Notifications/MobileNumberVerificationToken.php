<?php

namespace Javaabu\MobileVerification\Notifications;

use Illuminate\Notifications\Notification;
use Javaabu\SmsNotifications\Notifications\SendsSms;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

class MobileNumberVerificationToken extends Notification implements SmsNotification
{
    use SendsSms;

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * The name of the intended user
     */
    public string $name;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     * @param string $name
     */
    public function __construct(string $token, string $name = '')
    {
        $this->token = $token;
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
                'Your '.get_setting('app_name').' account verification code is '.$this->token;
    }
}
