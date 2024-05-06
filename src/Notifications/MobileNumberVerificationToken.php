<?php

namespace Javaabu\MobileVerification\Notifications;

use Illuminate\Notifications\Notification;

class MobileNumberVerificationToken extends Notification implements SmsNotification
{
    use SendsSms;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * The name of the intended user
     *
     * @var string
     */
    public $name;

    /**
     * Create a new notification instance.
     *
     * @param $token
     * @param string $name
     */
    public function __construct($token, $name = '')
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
    public function toSms($notifiable)
    {
        return  "Dear ".($this->name ?: 'User').",\n".
                'Your '.get_setting('app_name').' account verification code is '.$this->token;
    }
}
