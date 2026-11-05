<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Events\MobileNumberUpdated;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MobileNumberSettingsTest extends TestCase
{
    #[Test]
    public function guests_are_sent_to_the_login(): void
    {
        $this->get('/settings/mobile-number')->assertRedirect('/login');
        $this->get('/admin/settings/mobile-number')->assertRedirect('/admin/login');
    }

    #[Test]
    public function users_change_their_number_with_a_code(): void
    {
        Event::fake([MobileNumberUpdated::class]);

        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($user, 'web')
            ->get('/settings/mobile-number')
            ->assertOk()
            ->assertSee('data-current-number', false)
            ->assertSee('+9607771234');

        $this->post('/settings/mobile-number', ['number' => '9991234'])
            ->assertRedirect('/settings/mobile-number/verify');

        $this->get('/settings/mobile-number/verify')->assertOk()->assertSee('+9609991234');

        // Still the old number until the code is entered
        $this->assertSame('+9607771234', $user->fresh()->formatted_mobile_number);

        $this->post('/settings/mobile-number/verify', ['verification_code' => $this->sentCode('+9609991234')])
            ->assertRedirect('/settings/mobile-number')
            ->assertSessionHas('status', 'Your mobile number is now +9609991234.');

        $this->assertSame('+9609991234', $user->fresh()->formatted_mobile_number);

        Event::assertDispatched(MobileNumberUpdated::class, fn (MobileNumberUpdated $event) => $event->old_number === '+9607771234'
            && $event->causer->is($user));
    }

    #[Test]
    public function the_number_of_another_user_cannot_be_taken(): void
    {
        User::factory()->withMobileNumber('9991234')->create();
        $user = User::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($user, 'web')
            ->post('/settings/mobile-number', ['number' => '9991234'])
            ->assertSessionHasErrors(['number' => 'This mobile number is already registered.']);

        $this->post('/settings/mobile-number', ['number' => '7771234'])
            ->assertSessionHasErrors(['number' => 'This is already your mobile number.']);

        Notification::assertNothingSent();
    }

    #[Test]
    public function an_admin_can_use_a_number_a_user_has(): void
    {
        User::factory()->withMobileNumber('7771234')->create();
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'web_admin')
            ->post('/admin/settings/mobile-number', ['number' => '7771234'])
            ->assertRedirect('/admin/settings/mobile-number/verify');

        $this->post('/admin/settings/mobile-number/verify', ['verification_code' => $this->sentCodeTo($admin->getMorphClass())])
            ->assertRedirect('/admin/settings/mobile-number');

        $this->assertSame('+9607771234', $admin->fresh()->formatted_mobile_number);
    }

    #[Test]
    public function users_without_a_number_must_verify_one_before_continuing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get('/dashboard')
            ->assertRedirect('/settings/mobile-number');

        $this->get('/settings/mobile-number')
            ->assertOk()
            ->assertSee('data-no-number', false);

        $this->getJson('/dashboard')->assertForbidden();

        $this->post('/settings/mobile-number', ['number' => '7771234']);
        $this->post('/settings/mobile-number/verify', ['verification_code' => $this->sentCode()])
            ->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk()->assertSee('user dashboard');
    }

    #[Test]
    public function the_middleware_checks_the_users_of_its_type(): void
    {
        $admin = Admin::factory()->withMobileNumber('7771234')->create();

        $this->actingAs($admin, 'web_admin')->get('/admin')->assertOk();
    }

    #[Test]
    public function the_page_can_ask_for_the_password_first(): void
    {
        $this->app['router']->setRoutes(new RouteCollection);
        Route::authRoutes('user');
        Route::mobileNumberRoutes('user', ['confirm_password' => true]);
        $this->app['router']->getRoutes()->refreshNameLookups();

        $this->actingAs(User::factory()->create(), 'web')
            ->get('/settings/mobile-number')
            ->assertRedirect('/confirm-password');
    }

    protected function sentCodeTo(string $user_type): string
    {
        $code = null;

        Notification::assertSentTo(
            MobileNumber::query()->where('user_type', $user_type)->sole(),
            VerificationCodeNotification::class,
            function ($notification) use (&$code) {
                $code = $notification->verification_code;

                return true;
            },
        );

        return $code;
    }
}
