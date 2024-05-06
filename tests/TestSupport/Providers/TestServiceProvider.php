<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database');

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // morph map
        Relation::morphMap([
            'user' => User::class,
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
