<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class TokenLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // an unauthorized user cannot visit auth protected routes
    public function an_unauthorized_user_cannot_visit_auth_protected_routes()
    {
        $this->get(route('protected'))
            ->assertRedirect(route('login'));
    }


    /** @test */
    // An existing user can login using a token
    public function an_existing_user_can_login_using_a_token()
    {
        $this->get('/');

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $mobile_number = MobileNumber::factory()->create([
            'user_type' => 'user',
            'user_id' => $user->id,
            'number' => '752822'
        ]);

        $token = $mobile_number->generateToken();

        $this->post(route('login'), [
            'number' => '7528222',
            'token' => $token,
        ])->assertSessionHasNoErrors();

        $this->get(route('protected'))
            ->assertSee('Protected Route');

    }
}
