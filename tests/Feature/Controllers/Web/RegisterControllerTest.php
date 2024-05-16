<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestCase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
     * VERIFICATION CODE TESTS
     * */

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
            'purpose' => 'register',
        ]);

        $response->assertSessionHasErrors(['number']);

        Notification::assertNotSentTo(
            [$mobileNumber],
            MobileNumberVerificationToken::class
        );
    }

    // it can register a user if the token is correct
    /** @test */
    public function it_can_register_a_user_if_the_token_is_correct()
    {
        $mobileNumber = MobileNumber::factory()->create([
            'user_type' => 'user',
            'user_id' => null,
            'number' => '7528222',
            'token' => '123456',
        ]);

        $this->post(route('register'), [
            'number' => '7528222',
            'token' => '123456',
            'name' => 'John Doe',
            'email' => 'admin@example.com',
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


    // it will not register a user if the token is incorrect
}
