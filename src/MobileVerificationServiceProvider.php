<?php

namespace Javaabu\MobileVerification;

use Illuminate\Support\ServiceProvider;
use Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes;
use Javaabu\MobileVerification\Contracts\ValidatesNumberFormat;
use Javaabu\MobileVerification\Support\NumberFormatValidator;
use Javaabu\MobileVerification\Support\VerificationCodeGenerator;

class MobileVerificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mobile-verification.php', 'mobile-verification');

        $this->app->bindIf(GeneratesVerificationCodes::class, VerificationCodeGenerator::class);
        $this->app->bindIf(ValidatesNumberFormat::class, NumberFormatValidator::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        if (MobileVerification::$runs_migrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mobile-verification.php' => config_path('mobile-verification.php'),
        ], 'mobile-verification-config');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/mobile-verification'),
        ], 'mobile-verification-translations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mobile-verification-migrations');
    }
}
