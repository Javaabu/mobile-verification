<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use JsonException;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Notifications\LoginVerificationTokenNotification;

class MobileNumberUpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
     * VERIFICATION CODE TESTS
     * */

    public function test_can_see_the_update_verification_code_request_form() // done
    {
        $this->get('/mobile-verification/update')
             ->assertSee("Verification Code Request Form");
    }

    public function test_can_see_the_update_verification_code_form_if_mobile_number_is_present_in_session() // done
    {
        $mobile_number = MobileNumber::factory()->create([
            'number'       => '7326655',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => null,
        ]);

        $this->withSession(['mobile_to_update' => $mobile_number->id])
             ->get('/mobile-verification/update')
             ->assertSee("Enter Verification Code");
    }

    public function test_can_send_a_post_request_to_update_a_user_with_a_mobile_number() // done
    {
        $this->get('/mobile-numbers');

        $this->post('/mobile-verification/update', [
            'number' => '7326655',
        ])->assertSessionHasNoErrors();
    }

    public function test_if_the_number_provided_for_update_is_already_registered_then_user_is_redirected_back_with_errors()
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'number'       => '7326655',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        $response = $this->post('mobile-verification/update', [
            'number' => '7326655',
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['number']);
    }

    /* @throws JsonException */
    public function test_if_the_number_provided_for_update_is_valid_then_system_sends_an_otp_to_the_number()
    {
        $this->post('mobile-verification/update', [
            'number' => '7326655',
        ])
             ->assertSessionHasNoErrors();

        $mobile_number = MobileNumber::where('number', '7326655')->first();

        Notification::assertSentTo(
            [$mobile_number],
            MobileNumberVerificationToken::class
        );
    }

    /*
     * UPDATE MOBILE NUMBER TESTS
     * */
    /* @throws JsonException */
    public function test_user_can_update_mobile_number_with_valid_token()
    {
        $this->withoutExceptionHandling();
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

        $verification_code = $new_mobile_number->generateVerificationCode();

        $this->patch('mobile-verification/update', [
            'number' => '7326655',
            'verification_code' => $verification_code,
            'verification_code_id' => $new_mobile_number->verification_code_id->toString(),
        ])
             ->assertSessionHasNoErrors()
             ->assertRedirect();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7528222',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => null,
        ]);
    }
}
