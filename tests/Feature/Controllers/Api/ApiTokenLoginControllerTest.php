<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class ApiTokenLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    // mark as incomplete

    /** @test */
    // an unauthorized user cannot visit auth protected routes
    public function an_unauthorized_user_cannot_visit_auth_protected_routes()
    {
        $this->get('/api/protected')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }


    /** @test */
    // An existing user can login using a token

}
