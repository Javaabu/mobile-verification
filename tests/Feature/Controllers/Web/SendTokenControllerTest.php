<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class SendTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_see_the_verification_request_form() // done
    {
        $this->get('/mobile-numbers')
            ->assertSee("Verification Code Request Form");
    }

    public function test_can_see_the_verification_code_form_if_mobile_number_is_present() // done
    {
        $mobile_number = MobileNumber::factory()->create([
            'number' => '7326655',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => null,
        ]);

        $this->withSession(['mobile_number_to_login' => $mobile_number->id])
             ->get('/mobile-numbers')
             ->assertSee("Enter Verification Code");
    }

    public function test_can_send_a_post_request_to_login_a_user_with_a_mobile_number() // done
    {
        $this->get('/mobile-numbers');

        $this->post('/mobile-numbers', [
            'number' => '7326655',
        ])->assertSessionHasNoErrors();
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

        $response = $this->post('mobile', [
            'number' => $mobileNumber->number,
            'purpose' => 'register',
        ]);

        $response->assertSessionHasErrors(['number']);

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



}
