<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use JsonException;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;

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
        $mobile_number = MobileNumber::factory()->create([
            'number'       => '7528222',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        $this->actingAs($user);

        $this->post(route('request-number-change-otp'), ['number' => '7326655'])
             ->assertSessionHasNoErrors()
             ->assertStatus(200);

        $this->assertDatabaseHas('mobile_numbers', [
            'number'       => '7326655',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => null,
        ]);

//        Notification::assertSentTo(
//            [$mobile_number],
//            MobileNumberVerificationToken::class
//        );

    }
}
