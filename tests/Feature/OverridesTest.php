<?php

namespace Javaabu\MobileVerification\Tests\Feature;

use Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes;
use Javaabu\MobileVerification\Tests\Fixtures\CustomMobileLoginController;
use Javaabu\MobileVerification\Tests\Models\User;
use Javaabu\MobileVerification\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;

class OverridesTest extends TestCase
{
    #[Test]
    #[DefineEnvironment('useCustomController')]
    public function controllers_come_from_config(): void
    {
        $this->get('/login/mobile')->assertOk()->assertSee('custom mobile login for user');
        $this->get('/admin/login/mobile')->assertOk()->assertSee('custom mobile login for admin');
    }

    #[Test]
    public function the_code_generator_can_be_rebound(): void
    {
        $this->app->bind(GeneratesVerificationCodes::class, fn () => new class implements GeneratesVerificationCodes
        {
            public function generate(): string
            {
                return '424242';
            }
        });

        User::factory()->withMobileNumber('7771234')->create();

        $this->post('/login/mobile', ['number' => '7771234']);

        $this->assertSame('424242', $this->sentCode());
    }

    #[Test]
    public function views_can_be_overridden_by_the_app(): void
    {
        $this->app['view']->getFinder()->prependNamespace('mobile-verification', __DIR__ . '/../views/overrides');

        $this->get('/login/mobile')->assertOk()->assertSee('overridden mobile login');
    }

    protected function useCustomController($app): void
    {
        $app['config']->set('mobile-verification.controllers.login', CustomMobileLoginController::class);
    }
}
