<?php

namespace Javaabu\MobileVerification\Tests;

use Biscolab\ReCaptcha\Facades\ReCaptcha;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Traits\Dumpable;
use Javaabu\Activitylog\ActivitylogServiceProvider;
use Javaabu\Helpers\HelpersServiceProvider;
use Javaabu\MobileVerification\MobileVerificationServiceProvider;
use Javaabu\MobileVerification\Tests\TestSupport\Providers\TestServiceProvider;
use Javaabu\SmsNotifications\SmsNotificationsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use Dumpable;

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
            TestServiceProvider::class,
            SmsNotificationsServiceProvider::class,
            HelpersServiceProvider::class,
            ActivitylogServiceProvider::class,
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

    public function checkRule(mixed $rule, string $attribute, mixed $value): bool
    {
        $passed = true;
        $fail = function () use (&$passed) {
            $passed = false;
        };

        $rule->validate($attribute, $value, $fail);

        return $passed;
    }
}
