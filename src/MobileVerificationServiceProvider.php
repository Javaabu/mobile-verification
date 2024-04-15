<?php

namespace Javaabu\MobileVerification;

use Illuminate\Support\ServiceProvider;

class MobileVerificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/mobile-verification.php' => config_path('mobile-verification.php'),
            ], 'mobile-verification-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/mobile-verification.php', 'mobile-verification');
    }
}
