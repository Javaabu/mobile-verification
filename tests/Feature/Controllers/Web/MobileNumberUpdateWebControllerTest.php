<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Tests\Feature\Customer;
use Javaabu\MobileVerification\Tests\Feature\MobileNumber;
use Javaabu\MobileVerification\Tests\Feature\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Tests\Feature\User;
use Javaabu\MobileVerification\Tests\TestCase;

class MobileNumberUpdateWebControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_redirects_guest_users_to_the_login_page()
    {
        $this->get('/mobile-number-update')
            ->assertRedirect('/login');
    }

    /** @test */
    public function it_can_send_the_mobile_number_verification_code()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $this->post('/my/mobile-number', [
                'phone' => '7645530',
                'country_code' => '960',
            ])
            ->assertSessionMissing('errors')
            ->assertStatus(200)
            ->assertSee('We have sent a verification code to your number');

        $this->assertDatabaseHas('mobile_numbers', [
            'country_code' => '960',
            'number' => '7645530',
            'user_id' => null,
            'user_type' => 'customer',
        ]);

        $phone = MobileNumber::find(MobileNumber::max('id'));

        Notification::assertSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_wont_send_the_verification_code_to_a_duplicate_mobile_number()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $other_user = Customer::factory()->create();

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => $other_user->id,
        ]);

        $this->get('/my/mobile-number');

        $this->post('/my/mobile-number', [
            'phone' => '7645530',
            'country_code' => '960',
        ])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_id' => $other_user->id,
            'user_type' => 'customer',
        ]);

        Notification::assertNotSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_will_send_the_mobile_number_verification_code_to_another_types_duplicate_number()
    {
        $user = $this->getActiveCustomer();

        $other_type = User::factory()->create();

        $other_phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $other_type->id,
        ]);

        $this->actingAsCustomer($user);

        $this->post('/my/mobile-number', [
            'phone' => '7645530',
            'country_code' => '960',
        ])
            ->assertSessionMissing('errors')
            ->assertStatus(200)
            ->assertSee('We have sent a verification code to your number');

        $phone = MobileNumber::find(MobileNumber::max('id'));

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $other_phone->id,
            'country_code' => '960',
            'number' => '7645530',
            'user_id' => $other_type->id,
            'user_type' => 'user',
        ]);

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'country_code' => '960',
            'number' => '7645530',
            'user_id' => null,
            'user_type' => 'customer',
        ]);

        Notification::assertSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_will_send_the_mobile_number_verification_code_to_another_country_codes_duplicate_number()
    {
        $user = $this->getActiveCustomer();

        $other_user = Customer::factory()->create();

        $other_phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '60',
            'user_type' => 'customer',
            'user_id' => $other_user->id,
        ]);

        $this->actingAsCustomer($user);

        $this->post('/my/mobile-number', [
            'phone' => '7645530',
            'country_code' => '960',
        ])
            ->assertSessionMissing('errors')
            ->assertStatus(200)
            ->assertSee('We have sent a verification code to your number');

        $phone = MobileNumber::find(MobileNumber::max('id'));

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $other_phone->id,
            'country_code' => '60',
            'number' => '7645530',
            'user_id' => $other_user->id,
            'user_type' => 'customer',
        ]);

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'country_code' => '960',
            'number' => '7645530',
            'user_id' => null,
            'user_type' => 'customer',
        ]);

        Notification::assertSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_wont_send_the_verification_code_if_the_mobile_number_has_too_many_attempts()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'attempts' => 6,
            'user_type' => 'customer',
        ]);

        $this->get('/my/mobile-number');

        $response = $this->post('/my/mobile-number', [
                'phone' => '7645530',
                'country_code' => '960',
            ])
            ->assertSessionHasErrors('mobile_number');

        $this->get($response->headers->get('Location'))
            ->assertSee('Too many verification attempts.');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'attempts' => 6,
            'user_id' => null,
            'user_type' => 'customer',
        ]);

        Notification::assertNotSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_wont_send_the_verification_code_if_the_verification_code_was_sent_too_recently()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token_created_at' => Carbon::now(),
            'user_type' => 'customer',
        ]);

        $this->get('/my/mobile-number');

        $response = $this->post('/my/mobile-number', [
            'phone' => '7645530',
            'country_code' => '960',
        ])
            ->assertSessionHasErrors('mobile_number');

        $this->get($response->headers->get('Location'))
            ->assertSee('A verification code was sent to this number too recently. Please wait a few moments before resending.');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_id' => null,
            'user_type' => 'customer',
        ]);

        Notification::assertNotSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_wont_send_the_verification_code_if_the_mobile_number_is_already_taken()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $other_user = Customer::factory()->create();

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'attempts' => 6,
            'user_id' => $other_user->id,
            'user_type' => 'customer',
        ]);

        $this->post('/my/mobile-number', [
                'phone' => '7645530',
                'country_code' => '960',
            ])
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'attempts' => 6,
            'user_id' => $other_user->id,
            'user_type' => 'customer',
        ]);

        Notification::assertNotSentTo(
            [$phone],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_does_not_allow_the_mobile_number_to_be_verified_using_an_invalid_token()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
        ]);

        $this->get('/my/mobile-number');

        $this->patch('/my/mobile-number', [
                'phone_id' => $phone->id,
                'code' => '3456',
            ])
            // ->assertSessionHasErrors('mobile_number')
            ->assertSee('The code is invalid')
            ->assertSee('We have sent a verification code to your number');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => null,
        ]);
    }

    /** @test */
    public function it_does_not_allow_a_duplicate_mobile_number_to_be_verified()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $other_user = Customer::factory()->create();

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
            'user_id' => $other_user->id,
        ]);

        $this->get('/my/mobile-number');

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '3456',
        ])
            ->assertSee('The selected phone id is invalid');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => $other_user->id,
        ]);
    }

    /** @test */
    public function it_can_verify_another_types_duplicate_mobile_number()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $other_type = User::factory()->create();

        $other_phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'user',
            'user_id' => $other_type->id,
        ]);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
        ]);

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '123456',
        ])
            ->assertRedirect('/my/dashboard')
            ->assertSessionMissing('errors')
            ->assertSessionHas('alerts');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $other_phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $other_type->id,
        ]);
    }

    /** @test */
    public function it_can_verify_another_country_codes_duplicate_mobile_number()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $other_user = Customer::factory()->create();

        $other_phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '60',
            'token' => '123456',
            'user_type' => 'customer',
            'user_id' => $other_user->id,
        ]);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
        ]);

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '123456',
        ])
            ->assertRedirect('/my/dashboard')
            ->assertSessionMissing('errors')
            ->assertSessionHas('alerts');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $other_phone->id,
            'number' => '7645530',
            'country_code' => '60',
            'user_type' => 'customer',
            'user_id' => $other_user->id,
        ]);
    }

    /** @test */
    public function it_does_not_allow_the_mobile_number_to_be_verified_using_an_expired_token()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'token_created_at' => Carbon::now()->subDay(),
            'user_type' => 'customer',
        ]);

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '123456',
        ])
            //->assertSessionHasErrors('mobile_number')
            ->assertSee('The verification code for this number is expired')
            ->assertSee('We have sent a verification code to your number');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => null,
        ]);
    }

    /** @test */
    public function it_does_not_allow_a_locked_mobile_number_to_be_verified()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'attempts' => 6,
            'user_type' => 'customer',
        ]);

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '123456',
        ])
            //->assertSessionHasErrors('mobile_number')
            ->assertSee('Too many verification attempts')
            ->assertSee('We have sent a verification code to your number');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => null,
        ]);
    }

    /** @test */
    public function it_validates_the_mobile_number_verification_inputs()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
        ]);

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '',
        ])
            //->assertSessionHasErrors('code')
            ->assertSee('The code field is required')
            ->assertSee('We have sent a verification code to your number');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => null,
        ]);
    }

    /** @test */
    public function it_redirects_to_mobile_number_update_page_if_no_phone_id_is_provided()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
        ]);

        $response = $this->patch('/my/mobile-number', [
            'phone_id' => '',
            'code' => '123456',
        ])
            ->assertRedirect('/my/mobile-number');

        $this->get($response->headers->get('Location'))
            ->assertSee('Enter your new mobile number');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => null,
        ]);
    }

    /** @test */
    public function it_can_verify_a_mobile_number()
    {
        $user = $this->getActiveCustomer();

        $this->actingAsCustomer($user);

        $phone = MobileNumber::factory()->create([
            'number' => '7645530',
            'country_code' => '960',
            'token' => '123456',
            'user_type' => 'customer',
        ]);

        $this->patch('/my/mobile-number', [
            'phone_id' => $phone->id,
            'code' => '123456',
        ])
            ->assertRedirect('/my/dashboard')
            ->assertSessionMissing('errors')
            ->assertSessionHas('alerts');

        $this->assertDatabaseHas('mobile_numbers', [
            'id' => $phone->id,
            'number' => '7645530',
            'country_code' => '960',
            'user_type' => 'customer',
            'user_id' => $user->id,
        ]);
    }
}
