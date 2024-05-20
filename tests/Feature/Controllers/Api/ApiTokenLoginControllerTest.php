<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Javaabu\MobileVerification\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\MobileVerification\Models\MobileNumber;
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
    public function can_obtain_an_access_token_using_a_valid_verification_code()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $mobile_number = MobileNumber::create([
            'number' => '7528222',
            'country_code' => '960',
            'user_type' => 'user',
            'user_id' => $user->id,
        ]);

        $verification_code = $mobile_number->generateVerificationCode();


        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'mobile',
            'number' => '7528222',
            'country_code' => '960',
            'verification_code' => $verification_code,
        ]);

        dd($response->json());
        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

}
