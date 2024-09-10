<?php

namespace Javaabu\MobileVerification\Tests\Feature\Controllers\Web;

use Laravel\Passport\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\ClientRepository;
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
        Config::set('auth.guards.api.provider', 'users');
        Config::set('auth.guards.api.driver', 'passport');
        Config::set('auth.providers.users.model', User::class);

        $this->get('/api/protected')
             ->assertStatus(302)
             ->assertRedirect(route('login'));
    }


    /** @test */
    public function can_obtain_an_access_token_using_a_valid_verification_code()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        MobileNumber::unguard();

        $mobile_number = MobileNumber::create([
            'number'       => '7528222',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        $verification_code = $mobile_number->generateVerificationCode();

        $this->assertDatabaseHas('mobile_numbers', [
            'number'       => '7528222',
            'country_code' => '960',
            'user_id'      => $user->id,
            'user_type'    => 'user',
        ]);

        $grantClient = $this->app
            ->make(ClientRepository::class)
            ->createPasswordGrantClient(null, 'Test', 'http://localhost');

        Config::set('auth.guards.api.provider', 'users');
        Config::set('auth.providers.users.model', User::class);

        $response = $this->postJson('/oauth/token', [
            'grant_type'        => 'mobile',
            'client_id'         => $grantClient->id,
            'client_secret'     => $grantClient->secret,
            'number'            => '7528222',
            'country_code'      => '960',
            'verification_code' => $verification_code,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token_type', 'access_token', 'refresh_token', 'expires_in']);
    }

    public function test_authenticated_user_can_visit_protected_routes()
    {
        $user = User::factory()->create();
        $access_token = $this->getAccessToken($user);

        $response = $this->json('GET', '/api/protected', [], [
            'Authorization' => 'Bearer ' . $access_token,
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('Protected route');
    }

    public function getAccessToken(?User $user = null): string
    {
        $user ??= User::factory()->create();
        MobileNumber::unguard();

        $mobile_number = MobileNumber::create([
            'number'       => '7528222',
            'country_code' => '960',
            'user_type'    => 'user',
            'user_id'      => $user->id,
        ]);

        $verification_code = $mobile_number->generateVerificationCode();

        $grantClient = $this->app->make(ClientRepository::class)
                                 ->createPasswordGrantClient(null, 'Test', 'http://localhost');

        Config::set('auth.guards.api.provider', 'users');
        Config::set('auth.guards.api.driver', 'passport');
        Config::set('auth.providers.users.model', User::class);

        $response = $this->postJson('/oauth/token', [
            'grant_type'        => 'mobile',
            'client_id'         => $grantClient->id,
            'client_secret'     => $grantClient->secret,
            'number'            => '7528222',
            'country_code'      => '960',
            'verification_code' => $verification_code,
        ]);

        $content = json_decode($response->content(), true);
        return $content['access_token'];
    }

}
