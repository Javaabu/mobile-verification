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


    // it can register a user if the token is correct
    /** @test */
    public function it_can_register_a_user_if_the_token_is_correct()
    {
        $mobileNumber = MobileNumber::factory()->create([
            'user_type' => 'user',
            'user_id' => null,
            'number'  => '7528222',
            'token'   => '123456',
        ]);

        $this->post(route('register'), [
            'number' => '7528222',
            'token'  => '123456',
            'name'   => 'John Doe',
            'email'  => 'admin@example.com'
        ])
             ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name'  => 'John Doe',
            'email' => 'admin@example.com',
        ]);

        $user_id = User::max('id');

        $this->assertDatabaseHas('mobile_numbers', [
            'number'    => '7528222',
            'user_id'   => $user_id,
            'user_type' => 'user',
        ]);
    }


    // it will not register a user if the token is incorrect
}
