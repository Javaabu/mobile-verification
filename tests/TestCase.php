<?php

namespace Javaabu\MobileVerification\Tests;

use Biscolab\ReCaptcha\Facades\ReCaptcha;
use Illuminate\Support\Facades\Notification;
use Javaabu\MobileVerification\Tests\TestSupport\Providers\TestServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Javaabu\MobileVerification\MobileVerificationServiceProvider;

abstract class TestCase extends BaseTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        Notification::fake();
    }

    protected function getPackageProviders($app)
    {
        return [
            MobileVerificationServiceProvider::class,
            TestServiceProvider::class
        ];
    }

    protected function withRecaptchaPassing(): void
    {
        ReCaptcha::shouldReceive('validate')
            ->once()
            ->andReturnTrue();
    }

    protected function withRecaptchaFailing(): void
    {
        ReCaptcha::shouldReceive('validate')
            ->once()
            ->andReturnFalse();
    }
}
