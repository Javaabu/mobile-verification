<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    // can send a post request to register a user with a mobile number
    public function can_send_a_post_request_to_register_a_user_with_a_mobile_number()
    {
        $this->get('/');

        $this->post(route('mobile-number-otp'), [
            'number' => '7326655',
        ])
        ->assertSessionHasNoErrors();
    }

    /** @test */
    // if the number provided is already registered then the user is redirected back with an error
    public function if_the_number_provided_is_already_registered_then_the_user_is_redirected_back_with_an_error()
    {
        $user = User::factory()->create();
        $mobileNumber = MobileNumber::factory()->create([
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $this->post(route('mobile-number-otp'), [
            'number' => $mobileNumber->number,
        ])
        ->assertSessionHasErrors(['number']);

        Notification::assertNotSentTo(
            [$mobileNumber],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function if_the_number_provided_is_valid_then_system_sends_an_otp_to_the_number()
    {
        $this->assertDatabaseCount('mobile_numbers', 0);

        $this->post(route('mobile-number-otp'), [
            'number' => '7326655',
        ])
        ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => null,
        ]);

        $phone = MobileNumber::where('number', '7326655')->first();

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

        $phone = \App\Helpers\MobileNumber\MobileNumber::factory()->create([
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
            \App\Helpers\MobileNumber\Notifications\MobileNumberVerificationToken::class
        );
    }
}
