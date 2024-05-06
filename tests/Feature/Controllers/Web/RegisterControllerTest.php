<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    // can send a post request to register a user with a mobile number
    public function can_send_a_post_request_to_register_a_user_with_a_mobile_number()
    {
        $this->get('/');

        $this->post(route('register'), [
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

        $this->post(route('register'), [
            'number' => $mobileNumber->number,
        ])
        ->assertSessionHasErrors(['number']);
    }

    /** @test */
    public function if_the_number_provided_is_valid_then_system_sends_an_otp_to_the_number()
    {
        $this->assertDatabaseCount('mobile_numbers', 0);

        $this->post(route('register'), [
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

}
