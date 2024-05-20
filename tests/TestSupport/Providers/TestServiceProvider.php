<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use Javaabu\MobileVerification\Models\MobileNumber;
use Illuminate\Database\Eloquent\Relations\Relation;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../database',
            __DIR__ . '/../../../vendor/laravel/passport/database/migrations'
        ]);

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // morph map
        Relation::morphMap([
            'user' => User::class,
            'mobile_number' => MobileNumber::class,
        ]);

        Passport::loadKeysFrom(__DIR__ . '/../../passport-keys');
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
