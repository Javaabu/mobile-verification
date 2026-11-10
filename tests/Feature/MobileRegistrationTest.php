<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Illuminate\Auth\Events\Registered;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers;
use Javaabu\MobileVerification\Tests\Fixtures\CreateUserWithIdCard;
use Javaabu\MobileVerification\Tests\Fixtures\RegistrationFormWithIdCard;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MobileRegistrationTest extends TestCase
{
    #[Test]
    public function users_sign_up_with_a_verified_number(): void
    {
        Event::fake([Registered::class]);

        $this->get('/register/mobile')
            ->assertOk()
            ->assertSee('data-mobile-page="register"', false);

        $this->post('/register/mobile', ['number' => '7771234'])
            ->assertRedirect('/register/mobile/verify');

        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()])
            ->assertRedirect('/register/mobile/details');

        $this->get('/register/mobile/details')
            ->assertOk()
            ->assertSee('data-mobile-page="register-details"', false)
            ->assertSee('+9607771234')
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false);

        $this->post('/register/mobile/details', ['name' => 'އަޙްމަދު ޢަލީ', 'email' => 'Ahmed@Example.com'])
            ->assertRedirect('/dashboard');

        $user = User::query()->sole();

        $this->assertSame('އަޙްމަދު ޢަލީ', $user->name);
        $this->assertSame('ahmed@example.com', $user->email);
        $this->assertNull($user->password);
        $this->assertSame('+9607771234', $user->formatted_mobile_number);
        $this->assertAuthenticatedAs($user, 'web');

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    public function the_email_is_optional(): void
    {
        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        $this->post('/register/mobile/details', ['name' => 'Ahmed'])->assertRedirect('/dashboard');

        $this->assertNull(User::query()->sole()->email);
    }

    #[Test]
    public function the_details_are_validated(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        $this->from('/register/mobile/details')
            ->post('/register/mobile/details', ['email' => 'taken@example.com'])
            ->assertRedirect('/register/mobile/details')
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertGuest('web');
    }

    #[Test]
    public function registered_numbers_cannot_sign_up_again(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/register/mobile', ['number' => '7771234'])
            ->assertSessionHasErrors(['number' => 'This mobile number is already registered.']);

        Notification::assertNothingSent();
    }

    #[Test]
    public function a_number_taken_while_filling_in_the_details_is_refused(): void
    {
        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        User::factory()->create()->updateMobileNumber('7771234');

        $this->post('/register/mobile/details', ['name' => 'Ahmed'])
            ->assertRedirect('/register/mobile')
            ->assertSessionHasErrors('number');

        $this->assertGuest('web');
    }

    #[Test]
    public function the_details_page_needs_a_verified_number(): void
    {
        $this->get('/register/mobile/details')->assertRedirect('/register/mobile');

        $this->post('/register/mobile', ['number' => '7771234']);

        // Sent, not verified
        $this->get('/register/mobile/details')->assertRedirect('/register/mobile');
        $this->post('/register/mobile/details', ['name' => 'Ahmed'])->assertRedirect('/register/mobile');

        $this->assertSame(0, User::query()->count());
    }

    #[Test]
    public function the_verified_number_expires(): void
    {
        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        $this->travel(11)->minutes();

        $this->get('/register/mobile/details')->assertRedirect('/register/mobile');
    }

    #[Test]
    public function types_without_registration_have_no_mobile_registration(): void
    {
        $this->get('/admin/register/mobile')->assertNotFound();
    }

    #[Test]
    public function mobile_registration_can_be_turned_off(): void
    {
        config()->set('mobile-verification.registration', false);

        $this->refreshRoutes();

        $this->get('/register/mobile')->assertNotFound();
        $this->get('/login/mobile')->assertOk()->assertDontSee(url('/register/mobile'));
    }

    #[Test]
    public function apps_change_the_form_and_how_users_are_created(): void
    {
        config()->set('mobile-verification.registration_form', RegistrationFormWithIdCard::class);
        $this->app->bind(CreatesUsersFromMobileNumbers::class, CreateUserWithIdCard::class);

        $this->post('/register/mobile', ['number' => '7771234']);
        $this->post('/register/mobile/verify', ['verification_code' => $this->sentCode()]);

        $this->get('/register/mobile/details')->assertSee('name="id_card"', false);

        $this->post('/register/mobile/details', ['name' => 'Ahmed', 'id_card' => 'bad'])
            ->assertSessionHasErrors('id_card');

        $this->post('/register/mobile/details', ['name' => 'Ahmed', 'id_card' => 'A123456'])
            ->assertRedirect('/dashboard');

        $this->assertSame('Ahmed (A123456)', User::query()->sole()->name);
    }

    #[Test]
    public function it_answers_json(): void
    {
        $this->postJson('/register/mobile', ['number' => '7771234'])
            ->assertOk()
            ->assertJsonPath('is_registered', false);

        $this->postJson('/register/mobile/verify', ['verification_code' => $this->sentCode()])
            ->assertOk()
            ->assertJsonPath('verified', true)
            ->assertJsonPath('redirect', url('/register/mobile/details'));

        $this->postJson('/register/mobile/details', ['name' => 'Ahmed'])->assertCreated();
    }

    protected function refreshRoutes(): void
    {
        $this->app['router']->setRoutes(new RouteCollection);
        $this->registerRoutes();
        $this->app['router']->getRoutes()->refreshNameLookups();
    }
}
