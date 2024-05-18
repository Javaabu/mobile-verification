<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Notifications\LoginVerificationTokenNotification;
use Javaabu\MobileVerification\Notifications\RegisterVerificationTokenNotification;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
     * VERIFICATION CODE TESTS
     * */
    public function test_can_see_the_verification_request_form() // done
    {
        $this->get('/mobile-verification/register')
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

        $this->withSession(['mobile_number_to_register' => $mobile_number->id])
             ->get('/mobile-verification/register')
             ->assertSee("Enter Verification Code");
    }

    /** @test */
    public function if_the_number_provided_is_already_registered_then_the_user_is_redirected_back_with_an_error()
    {
        $user = User::factory()->create();
        $mobileNumber = MobileNumber::factory()->create([
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $response = $this->post('mobile-verification/register', [
            'number' => $mobileNumber->number,
        ]);

        $response->assertSessionHasErrors(['number']);

        Notification::assertNotSentTo(
            [$mobileNumber],
            MobileNumberVerificationToken::class
        );
    }

    /** @test */
    public function it_can_send_registration_verification_code_if_the_number_is_not_taken()
    {
        $response = $this->post('mobile-verification/register', [
            'number' => '7528222'
        ]) ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7528222',
            'country_code' => MobileVerification::defaultCountryCode(),
            'user_type' => 'user',
            'user_id' => null,
        ]);

        $mobile_number = MobileNumber::where('number', '7528222')->first();

        Notification::assertSentTo(
            [$mobile_number],
            RegisterVerificationTokenNotification::class
        );
    }

    /*
     * REGISTRATION TESTS
     * */

    // it can register a user if the token is correct
    /** @test */
    public function it_can_register_a_user_if_the_token_is_correct()
    {
        $mobileNumber = MobileNumber::factory()->create([
            'user_type' => 'user',
            'user_id' => null,
            'number' => '7528222',
        ]);

        $verification_code = $mobileNumber->generateVerificationCode();

        $this->patch('mobile-verification/register', [
            'number' => '7528222',
            'name' => 'John Doe',
            'email' => 'admin@example.com',
            'verification_code' => $verification_code,
            'verification_code_id' => $mobileNumber->verification_code_id?->toString(),
        ])
             ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'admin@example.com',
        ]);

        $user_id = User::max('id');

        $this->assertDatabaseHas('mobile_numbers', [
            'number' => '7528222',
            'user_id' => $user_id,
            'user_type' => 'user',
        ]);
    }
}
