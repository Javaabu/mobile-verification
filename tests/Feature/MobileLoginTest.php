<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\Events\MobileNumberLocked;
use Javaabu\MobileVerification\Events\VerificationCodeSent;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MobileLoginTest extends TestCase
{
    #[Test]
    public function the_login_page_links_to_the_mobile_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-login-method="mobile"', false)
            ->assertSee(url('/login/mobile'));

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee(url('/admin/login/mobile'));
    }

    #[Test]
    public function the_mobile_login_page_renders(): void
    {
        $this->get('/login/mobile')
            ->assertOk()
            ->assertSee('data-mobile-page="login"', false)
            ->assertSee('name="number"', false)
            ->assertSee('+960')
            ->assertSee(url('/register/mobile'));

        // Admins can't register
        $this->get('/admin/login/mobile')
            ->assertOk()
            ->assertDontSee(url('/admin/register/mobile'));
    }

    #[Test]
    public function users_log_in_with_a_code(): void
    {
        Event::fake([VerificationCodeSent::class]);

        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234', 'remember' => '1'])
            ->assertRedirect('/login/mobile/verify')
            ->assertSessionHas('status');

        Event::assertDispatched(VerificationCodeSent::class, fn (VerificationCodeSent $event) => $event->purpose === Purposes::LOGIN);

        $code = $this->sentCode();

        $this->get('/login/mobile/verify')
            ->assertOk()
            ->assertSee('data-mobile-page="verify"', false)
            ->assertSee('+9607771234')
            ->assertSee('autocomplete="one-time-code"', false);

        $this->post('/login/mobile/verify', ['verification_code' => $code])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('web_admin');
        $this->assertNotNull($user->fresh()->getRememberToken());
        $this->assertFalse($user->phone->fresh()->hasVerificationCode());
    }

    #[Test]
    public function admins_log_in_on_their_own_guard(): void
    {
        $admin = Admin::factory()->withMobileNumber('7771234')->create();
        User::factory()->withMobileNumber('7779999')->create();

        $this->post('/admin/login/mobile', ['number' => '7771234'])
            ->assertRedirect('/admin/login/mobile/verify');

        $this->post('/admin/login/mobile/verify', ['verification_code' => $this->sentCode()])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'web_admin');
        $this->assertGuest('web');
    }

    #[Test]
    public function a_user_number_does_not_log_in_an_admin(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->from('/admin/login/mobile')
            ->post('/admin/login/mobile', ['number' => '7771234'])
            ->assertRedirect('/admin/login/mobile')
            ->assertSessionHasErrors(['number' => 'There is no account with this mobile number.']);

        Notification::assertNothingSent();
    }

    #[Test]
    public function the_number_must_be_valid(): void
    {
        $this->from('/login/mobile')
            ->post('/login/mobile', ['number' => '1234'])
            ->assertSessionHasErrors('number');

        $this->post('/login/mobile', ['number' => '7771234', 'country_code' => '44'])
            ->assertSessionHasErrors('country_code');

        $this->post('/login/mobile', [])->assertSessionHasErrors('number');
    }

    #[Test]
    public function a_wrong_code_counts_and_locks_the_number(): void
    {
        Event::fake([MobileNumberLocked::class]);

        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);
        $code = $this->sentCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        $this->from('/login/mobile/verify')
            ->post('/login/mobile/verify', ['verification_code' => $wrong])
            ->assertRedirect('/login/mobile/verify')
            ->assertSessionHasErrors(['verification_code' => 'The code is incorrect. You have 4 attempts left.']);

        foreach (range(1, 3) as $attempt) {
            $this->post('/login/mobile/verify', ['verification_code' => $wrong]);
        }

        $this->post('/login/mobile/verify', ['verification_code' => $wrong])
            ->assertSessionHasErrors(['verification_code' => 'This number is locked after too many wrong codes. Try again in 30 minutes.']);

        Event::assertDispatched(MobileNumberLocked::class);

        // Even the right code doesn't work now
        $this->post('/login/mobile/verify', ['verification_code' => $code])
            ->assertSessionHasErrors('verification_code');

        $this->assertGuest('web');

        // Nor can a new code be sent
        $this->travel(1)->minutes();

        $this->post('/login/mobile', ['number' => '7771234'])
            ->assertSessionHasErrors(['number' => 'This number is locked after too many wrong codes. Try again in 29 minutes.']);
    }

    #[Test]
    public function codes_expire(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);
        $code = $this->sentCode();

        $this->travel(11)->minutes();

        $this->post('/login/mobile/verify', ['verification_code' => $code])
            ->assertSessionHasErrors(['verification_code' => 'The code has expired. Ask for a new code.']);

        $this->assertGuest('web');
    }

    #[Test]
    public function an_older_code_is_no_longer_valid_after_a_new_one_is_sent(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);
        $first = $this->sentCode();

        // Another browser asks for a code for the same number
        $this->travel(31)->seconds();
        $other = MobileNumber::query()->first();
        $other->generateVerificationCode();

        $this->post('/login/mobile/verify', ['verification_code' => $first])
            ->assertSessionHasErrors(['verification_code' => 'This code is no longer valid. Ask for a new code.']);
    }

    #[Test]
    public function a_new_code_can_be_sent_after_the_resend_interval(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);

        $this->from('/login/mobile/verify')
            ->post('/login/mobile/resend')
            ->assertSessionHasErrors('verification_code');

        $this->get('/login/mobile/verify')->assertSee('data-resend-in', false);

        $this->travel(31)->seconds();

        $this->from('/login/mobile/verify')
            ->post('/login/mobile/resend')
            ->assertRedirect('/login/mobile/verify')
            ->assertSessionHasNoErrors();

        Notification::assertSentTimes(VerificationCodeNotification::class, 2);

        // The newest code logs in
        $this->post('/login/mobile/verify', ['verification_code' => $this->lastCode()])
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function the_code_page_needs_a_pending_number(): void
    {
        $this->get('/login/mobile/verify')->assertRedirect('/login/mobile');

        $this->post('/login/mobile/verify', ['verification_code' => '123456'])
            ->assertRedirect('/login/mobile')
            ->assertSessionHasErrors('number');
    }

    #[Test]
    public function two_factor_authentication_still_applies(): void
    {
        $user = User::factory()->withTwoFactor()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);

        $this->post('/login/mobile/verify', ['verification_code' => $this->sentCode()])
            ->assertRedirect('/two-factor-challenge')
            ->assertSessionHas(StartAuthenticatedSession::CHALLENGE_SESSION_KEY);

        $this->assertGuest('web');
    }

    #[Test]
    public function the_two_factor_challenge_can_be_skipped(): void
    {
        config()->set('mobile-verification.skip_two_factor', true);

        $user = User::factory()->withTwoFactor()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);

        $this->post('/login/mobile/verify', ['verification_code' => $this->sentCode()])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user, 'web');
    }

    #[Test]
    public function signed_in_users_are_sent_home(): void
    {
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($user, 'web')->get('/login/mobile')->assertRedirect('/dashboard');
    }

    #[Test]
    public function it_answers_json(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->postJson('/login/mobile', ['number' => '7771234'])
            ->assertOk()
            ->assertJsonPath('formatted_number', '+9607771234')
            ->assertJsonPath('is_registered', true)
            ->assertJsonPath('expires_in', 600)
            ->assertJsonStructure(['verification_code_id', 'resend_in', 'remaining_attempts']);

        $this->postJson('/login/mobile/resend')
            ->assertStatus(429)
            ->assertJsonValidationErrors('verification_code');

        $this->postJson('/login/mobile', ['number' => '7770000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('number');

        $this->postJson('/login/mobile/verify', ['verification_code' => $this->sentCode()])
            ->assertOk()
            ->assertJsonPath('two_factor', false)
            ->assertJsonPath('redirect', url('/dashboard'));
    }

    #[Test]
    public function requests_for_codes_are_rate_limited(): void
    {
        config()->set('mobile-verification.rate_limits.send', 2);

        $this->post('/login/mobile', ['number' => '7770001']);
        $this->post('/login/mobile', ['number' => '7770002']);
        $this->post('/login/mobile', ['number' => '7770003'])->assertStatus(429);
    }

    #[Test]
    public function extra_rules_can_guard_the_code_request(): void
    {
        config()->set('mobile-verification.request_code_rules', ['captcha' => ['required']]);

        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234'])->assertSessionHasErrors('captcha');

        Notification::assertNothingSent();
    }

    protected function lastCode(): string
    {
        $codes = [];

        Notification::assertSentTo(MobileNumber::query()->first(), VerificationCodeNotification::class, function (VerificationCodeNotification $notification) use (&$codes) {
            $codes[] = $notification->verification_code;

            return true;
        });

        return end($codes);
    }
}
