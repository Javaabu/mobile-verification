<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use JsonException;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Notifications\LoginVerificationTokenNotification;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
     * VERIFICATION CODE TESTS
     * */

    public function test_can_see_the_verification_request_form() // done
    {
        $this->get('/mobile-verification/login')
             ->assertSee("Verification Code Request Form");
    }

    public function test_can_see_the_verification_code_form_if_mobile_number_is_present_in_session() // done
    {
        $mobile_number = MobileNumber::factory()->create([
            'number'       => '7326655',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => null,
        ]);

        $this->withSession(['mobile_number_to_login' => $mobile_number->id])
             ->get('/mobile-verification/login')
             ->assertSee("Enter Verification Code");
    }

    public function test_can_send_a_post_request_to_login_a_user_with_a_mobile_number() // done
    {
        $this->get('/mobile-numbers');

        $this->post('/mobile-verification/login', [
            'number' => '7326655',
        ])->assertSessionHasNoErrors();
    }

    public function test_if_the_number_provided_is_not_registered_then_guest_is_redirected_back_with_errors()
    {
        $response = $this->post('mobile-verification/login', [
            'number' => '7326655',
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['number']);
    }

    /* @throws JsonException */
    public function test_if_the_number_provided_is_valid_then_system_sends_an_otp_to_the_number()
    {
        $user = User::factory()->create();
        $mobile_number = MobileNumber::factory()->create([
            'number'       => '7326655',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        $this->post('mobile-verification/login', [
            'number' => '7326655',
        ])
             ->assertSessionHasNoErrors();

        Notification::assertSentTo(
            [$mobile_number],
            LoginVerificationTokenNotification::class
        );
    }

    /*
     * LOGIN TESTS
     * */

    /** @test */
    public function an_unauthorized_user_cannot_visit_auth_protected_routes()
    {
        $this->get('/mobile-verification/protected')
             ->assertStatus(302)
             ->assertRedirect('/login');
    }

    /** @test */
    // An existing user can login using a token
    public function an_existing_user_can_login_using_a_token()
    {
        $user = User::factory()->create([
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $mobile_number = MobileNumber::factory()->create([
            'user_type' => 'user',
            'user_id'   => $user->id,
            'number'    => '7528222',
            'country_code' => MobileVerification::defaultCountryCode(),
        ]);

        $verification_code = $mobile_number->generateVerificationCode();

        $this->patch('/mobile-verification/login', [
            'number' => '7528222',
            'verification_code'  => $verification_code,
            'verification_code_id' => $mobile_number->verification_code_id?->toString(),
        ])->assertSessionHasNoErrors();

        $this->get('mobile-verification/protected')
             ->assertSee('Protected route');
    }
}
