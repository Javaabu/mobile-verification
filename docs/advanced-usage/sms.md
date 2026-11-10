---
title: The SMS
sidebar_position: 3
---

Codes are sent as `Javaabu\MobileVerification\Notifications\VerificationCodeNotification` to the number (the `MobileNumber` model is notifiable), on the channel of javaabu/sms-notifications' `sms.default` driver.

The message, in the locale of the request:

> Your Javaabu verification code is 123456. It expires in 10 minutes. Do not share it with anyone.

> Javaabu ގެ ވެރިފިކޭޝަން ކޯޑަކީ 123456. މި ކޯޑުގެ މުއްދަތު 10 މިނެޓުން ހަމަވާނެއެވެ. މި ކޯޑު އެހެން މީހަކާ ހިއްސާ ނުކުރައްވާ.

`:app` is `config('app.name')`. Thaana messages are sent as Unicode, so each SMS part holds fewer characters (70 rather than 160); keep a long app name in mind.

# Changing the message

Change the translation in your `lang/dv.json` or `lang/en.json` (same key), or extend the class:

```php
namespace App\Notifications;

use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification as BaseNotification;

class VerificationCodeNotification extends BaseNotification
{
    public function toSms($notifiable): string
    {
        return match ($this->purpose) {
            Purposes::LOGIN => __(':code is your login code for :app', ['code' => $this->verification_code, 'app' => config('app.name')]),
            default => parent::toSms($notifiable),
        };
    }
}
```

```php
// config/mobile-verification.php
'notification' => App\Notifications\VerificationCodeNotification::class,
```

The constructor gets the code, the purpose (`Purposes::LOGIN`, `REGISTER`, `UPDATE`) and the user's name when there is one. Add `ShouldQueue` to send it on the queue.

# Testing

```php
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;

Notification::fake();

$this->post('/login/mobile', ['number' => '7771234']);

$code = null;

Notification::assertSentTo(
    MobileNumber::query()->hasNumber('7771234')->sole(),
    VerificationCodeNotification::class,
    function (VerificationCodeNotification $notification) use (&$code) {
        $code = $notification->verification_code;

        return true;
    },
);

$this->post('/login/mobile/verify', ['verification_code' => $code])->assertRedirect('/dashboard');
```

Or bind a fixed code generator in tests:

```php
use Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes;

$this->app->bind(GeneratesVerificationCodes::class, fn () => new class implements GeneratesVerificationCodes {
    public function generate(): string { return '123456'; }
});
```
