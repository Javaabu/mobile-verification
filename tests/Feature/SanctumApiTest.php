<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use Javaabu\MobileVerification\Tests\Models\Sanctum\User;
use Javaabu\MobileVerification\Tests\TestCase;
use Laravel\Sanctum\SanctumServiceProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('optional')]
class SanctumApiTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), SanctumServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('javaabu-auth.api', 'sanctum');
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('auth.guards.sanctum', ['driver' => 'sanctum', 'provider' => null]);
    }

    protected function defineRoutes($router): void
    {
        $this->registerRoutes();

        Route::mobileApiRoutes('user');
        Route::middleware(['api', 'auth:sanctum'])->get('api/me', fn () => request()->user()->name);
    }

    #[Test]
    public function apps_get_a_token_for_a_number_and_code(): void
    {
        User::factory()->withMobileNumber('7771234')->create(['name' => 'Ahmed']);

        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])
            ->assertOk()
            ->assertJsonPath('formatted_number', '+9607771234')
            ->json('verification_code_id');

        $token = $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => $this->sentCode(),
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
        ])
            ->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->json('token');

        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertOk()->assertSee('Ahmed');
    }

    #[Test]
    public function a_wrong_code_gets_no_token(): void
    {
        User::factory()->withMobileNumber('7771234')->create();

        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])->json('verification_code_id');

        $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => 'nope',
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
        ])->assertUnprocessable()->assertJsonValidationErrors('verification_code');

        $this->postJson('/api/mobile/token', [
            'number' => '7770000',
            'verification_code' => '123456',
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
        ])->assertUnprocessable()->assertJsonValidationErrors('number');
    }

    #[Test]
    public function two_factor_users_also_send_their_authenticator_code(): void
    {
        User::factory()->withTwoFactor()->withMobileNumber('7771234')->create();

        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])->json('verification_code_id');

        $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => $this->sentCode(),
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
        ])->assertUnprocessable()->assertJsonValidationErrors('code');

        $this->travel(31)->seconds();
        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])->json('verification_code_id');

        $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => $this->lastSentCode(),
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
            'code' => '000000',
        ])->assertUnprocessable()->assertJsonValidationErrors('code');

        $this->travel(31)->seconds();
        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])->json('verification_code_id');

        $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => $this->lastSentCode(),
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
            'recovery_code' => 'nope',
        ])->assertUnprocessable()->assertJsonValidationErrors('recovery_code');

        // The SMS code was used up, so ask again with a recovery code
        $this->travel(31)->seconds();

        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])->json('verification_code_id');

        $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => $this->lastSentCode(),
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
            'recovery_code' => 'recovery-code-1',
        ])->assertCreated();
    }

    #[Test]
    public function banned_users_get_no_token(): void
    {
        User::factory()->banned()->withMobileNumber('7771234')->create();

        $code_id = $this->postJson('/api/mobile/code', ['number' => '7771234'])->json('verification_code_id');

        $this->postJson('/api/mobile/token', [
            'number' => '7771234',
            'verification_code' => $this->sentCode(),
            'verification_code_id' => $code_id,
            'device_name' => 'iPhone',
        ])->assertUnprocessable()->assertJsonValidationErrors('number');
    }

    protected function lastSentCode(): string
    {
        $codes = [];

        Notification::assertSentTo(
            MobileNumber::query()->sole(),
            VerificationCodeNotification::class,
            function ($notification) use (&$codes) {
                $codes[] = $notification->verification_code;

                return true;
            },
        );

        return end($codes);
    }
}
