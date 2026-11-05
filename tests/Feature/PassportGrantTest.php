<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\Models\Passport\User;
use Javaabu\MobileVerification\Tests\TestCase;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('optional')]
class PassportGrantTest extends TestCase
{
    protected static ?string $key_path = null;

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), PassportServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $config = $app['config'];

        $config->set('auth.providers.users.model', User::class);
        $config->set('auth.guards.api', ['driver' => 'passport', 'provider' => 'users']);

        Passport::loadKeysFrom(static::keyPath());
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/laravel/passport/database/migrations');
    }

    protected function defineRoutes($router): void
    {
        $this->registerRoutes();

        Route::middleware(['api', 'auth:api'])->get('api/me', fn () => request()->user()->name);
    }

    #[Test]
    public function the_mobile_grant_issues_tokens(): void
    {
        User::factory()->withMobileNumber('7771234')->create(['name' => 'Ahmed']);
        $client = app(ClientRepository::class)->createPasswordGrantClient('App', 'users');

        $code_id = $this->postJson('/login/mobile', ['number' => '7771234'])->json('verification_code_id');

        $response = $this->post('/oauth/token', [
            'grant_type' => 'mobile',
            'client_id' => $client->getKey(),
            'client_secret' => $client->plainSecret,
            'number' => '7771234',
            'verification_code' => $this->sentCode(),
            'verification_code_id' => $code_id,
        ])->assertOk();

        $this->assertNotEmpty($response->json('refresh_token'));

        $this->app['auth']->forgetGuards();

        $this->withToken($response->json('access_token'))->getJson('/api/me')->assertOk()->assertSee('Ahmed');
    }

    #[Test]
    public function a_wrong_code_is_refused(): void
    {
        User::factory()->withMobileNumber('7771234')->create();
        $client = app(ClientRepository::class)->createPasswordGrantClient('App', 'users');

        $this->postJson('/login/mobile', ['number' => '7771234']);

        $this->post('/oauth/token', [
            'grant_type' => 'mobile',
            'client_id' => $client->getKey(),
            'client_secret' => $client->plainSecret,
            'number' => '7771234',
            'verification_code' => 'wrong',
        ])->assertStatus(400)->assertJsonPath('error', 'invalid_request');
    }

    #[Test]
    public function clients_of_providers_without_a_user_type_are_refused(): void
    {
        User::factory()->withMobileNumber('7771234')->create();
        $client = app(ClientRepository::class)->createPasswordGrantClient('App', 'customers');

        $this->post('/oauth/token', [
            'grant_type' => 'mobile',
            'client_id' => $client->getKey(),
            'client_secret' => $client->plainSecret,
            'number' => '7771234',
            'verification_code' => '123456',
        ])->assertStatus(401)->assertJsonPath('error', 'invalid_client');
    }

    protected static function keyPath(): string
    {
        if (static::$key_path) {
            return static::$key_path;
        }

        $path = sys_get_temp_dir() . '/javaabu-mobile-verification-passport-' . getmypid();

        if (! is_dir($path)) {
            mkdir($path);
        }

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $private);

        file_put_contents("{$path}/oauth-private.key", $private);
        file_put_contents("{$path}/oauth-public.key", openssl_pkey_get_details($key)['key']);
        chmod("{$path}/oauth-private.key", 0600);
        chmod("{$path}/oauth-public.key", 0600);

        return static::$key_path = $path;
    }
}
