<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use JsonException;

class MobileNumberUpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_needs_to_be_logged_in_to_request_for_mobile_number_update_token()
    {
        $this->post(route('request-number-change-otp'))
             ->assertRedirect(route('login'));
    }

    public function test_user_needs_to_be_logged_in_to_update_mobile_number()
    {
        $response = $this->post(route('update-mobile-number'));
        $response->assertRedirect(route('login'));
    }


    /* @throws JsonException */
    public function test_user_can_request_for_otp_to_be_sent_to_new_mobile_number()
    {
        $user = User::factory()->create();
        MobileNumber::factory()->create([
            'number' => '7528222',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $this->post(route('request-number-change-otp'), ['number' => '7326655'])
             ->assertSessionHasNoErrors()
             ->assertRedirect();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => null,
        ]);

        $mobile_number = MobileNumber::where('number', '7326655')->first();

        Notification::assertSentTo(
            [$mobile_number],
            MobileNumberVerificationToken::class
        );
    }

    // test that user can update mobile number with valid token
    public function test_user_can_update_mobile_number_with_valid_token()
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'number' => '7528222',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $new_mobile_number = MobileNumber::factory()->create([
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => null,
        ]);

        $this->actingAs($user);

        $this->post(route('update-mobile-number'), [
            'number' => '7326655',
            'token' => $new_mobile_number->generateToken(),
        ])
             ->assertSessionHasNoErrors()
             ->assertRedirect();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);
    }
}
