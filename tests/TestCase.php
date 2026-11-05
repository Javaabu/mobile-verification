<?php

namespace Javaabu\MobileVerification\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Javaabu\Auth\AuthServiceProvider;
use Javaabu\Auth\UserType;
use Javaabu\Auth\UserTypes;
use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Manifest\Facades\Manifest;
use Javaabu\Manifest\ManifestServiceProvider;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\MobileVerificationServiceProvider;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use Javaabu\MobileVerification\Tests\Models\Admin;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\Permissions\PermissionsServiceProvider;
use Javaabu\SmsNotifications\SmsNotificationsServiceProvider;
use Javaabu\Stencil\StencilServiceProvider;
use Laravel\Passkeys\PasskeysServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Manifest::fake();
        Notification::fake();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PermissionServiceProvider::class,
            PermissionsServiceProvider::class,
            ManifestServiceProvider::class,
            FormsServiceProvider::class,
            StencilServiceProvider::class,
            PasskeysServiceProvider::class,
            AuthServiceProvider::class,
            SmsNotificationsServiceProvider::class,
            MobileVerificationServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];

        $config->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');
        $config->set('app.url', 'http://localhost');
        $config->set('app.name', 'Javaabu');
        $config->set('passkeys.user_handle_secret', 'test-secret');
        $config->set('session.serialization', 'php');
        $config->set('view.paths', [__DIR__ . '/views', ...$config->get('view.paths', [])]);

        $config->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'web_admin' => ['driver' => 'session', 'provider' => 'admins'],
        ]);

        $config->set('auth.providers', [
            'users' => ['driver' => 'eloquent', 'model' => User::class],
            'admins' => ['driver' => 'eloquent', 'model' => Admin::class],
        ]);

        $config->set('auth.passwords', [
            'users' => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 0],
            'admins' => ['provider' => 'admins', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 0],
        ]);

        $config->set('javaabu-auth.user_types', [
            'user' => [
                'guard' => 'web',
                'passwords' => 'users',
                'route_name' => '',
                'home' => '/dashboard',
                'features' => ['email_verification' => false],
            ],
            'admin' => [
                'guard' => 'web_admin',
                'passwords' => 'admins',
                'home' => '/admin',
                'features' => ['registration' => false, 'email_verification' => false],
            ],
        ]);

        $config->set('javaabu-auth.layouts', [
            'auth' => 'layouts.app',
            'account' => 'layouts.app',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database');
    }

    protected function userType(string $name = 'user'): UserType
    {
        return UserTypes::get($name);
    }

    /**
     * The code in the last SMS sent to a number
     */
    protected function sentCode(string $formatted_number = '+9607771234'): ?string
    {
        $code = null;

        Notification::assertSentTo(
            MobileVerification::mobileNumberModel()::query()->get()->first(fn ($number) => $number->formatted_number === $formatted_number),
            VerificationCodeNotification::class,
            function (VerificationCodeNotification $notification) use (&$code) {
                $code = $notification->verification_code;

                return true;
            },
        );

        return $code;
    }
}
