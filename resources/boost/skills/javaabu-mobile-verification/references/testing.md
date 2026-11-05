# Testing

Fake notifications and read the code from the SMS:

```php
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use PHPUnit\Framework\Attributes\Test;

#[Test]
public function users_log_in_with_their_mobile_number(): void
{
    Notification::fake();

    $user = User::factory()->create();
    $user->updateMobileNumber('7771234');

    $this->post('/login/mobile', ['number' => '7771234'])
        ->assertRedirect('/login/mobile/verify');

    $code = null;

    Notification::assertSentTo(
        MobileNumber::query()->hasNumber('7771234')->sole(),
        VerificationCodeNotification::class,
        function (VerificationCodeNotification $notification) use (&$code) {
            $code = $notification->verification_code;

            return true;
        },
    );

    $this->post('/login/mobile/verify', ['verification_code' => $code])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user, 'web');
}
```

Or bind a fixed code:

```php
$this->app->bind(\Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes::class, fn () => new class implements \Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes {
    public function generate(): string { return '123456'; }
});
```

Factories:

```php
MobileNumber::factory()->forUser($user)->create(['number' => '7771234']);
MobileNumber::factory()->forUser($user)->locked()->create();
```

Time: codes expire after `verification_code_validity` minutes and can be resent after `resend_interval` seconds; use `$this->travel(31)->seconds()` and `$this->freezeSecond()` for exact counts.

Test with Thaana for user-facing text (`$this->app->setLocale('dv')`), and for every user type that has mobile login.
