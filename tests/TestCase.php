<?php

namespace Javaabu\MobileVerification\Tests;

use Composer\Semver\VersionParser;
use Illuminate\Support\Facades\View;
use Biscolab\ReCaptcha\Facades\ReCaptcha;
use Javaabu\Helpers\HelpersServiceProvider;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Javaabu\Activitylog\ActivitylogServiceProvider;
use Javaabu\SmsNotifications\SmsNotificationsServiceProvider;
use Javaabu\MobileVerification\MobileVerificationServiceProvider;
use Javaabu\MobileVerification\Tests\TestSupport\Providers\TestServiceProvider;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        Notification::fake();

        View::addLocation(__DIR__ . '/TestSupport/views');
    }

    protected function getPackageProviders($app)
    {
        return [
            MobileVerificationServiceProvider::class,
            TestServiceProvider::class,
            SmsNotificationsServiceProvider::class,
            HelpersServiceProvider::class,
            ActivitylogServiceProvider::class,
            PassportServiceProvider::class,
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
