<?php

namespace Javaabu\MobileVerification;

use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Javaabu\MobileVerification\GrantType\MobileGrant;
use Javaabu\MobileVerification\Middlewares\AllowMobileVerifiedUsersOnly;

class MobileVerificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'mobile-verification-migrations');

            $this->publishes([
                __DIR__ . '/../config/mobile-verification.php' => config_path('mobile-verification.php'),
            ], 'mobile-verification-config');

            $this->publishes([
                __DIR__ . '/../lang' => lang_path('vendor/mobile-verification'),
            ], 'mobile-verification-translations');

        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mobile-verification');
    }

    /**
     * Register migration files.
     *
     * @return void
     */
    protected function registerMigrations(): void
    {
        if (MobileVerification::$runsMigrations) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../config/mobile-verification.php', 'mobile-verification');

        $this->app->resolving(AuthorizationServer::class, function (AuthorizationServer $server) {
            $grant = $this->makeGrant();
            $server->enableGrantType($grant, Passport::tokensExpireIn());
        });

        app('router')->aliasMiddleware('mobile-verified', AllowMobileVerifiedUsersOnly::class);
    }

    protected function makeGrant(): MobileGrant
    {
        $grant = new MobileGrant(
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }
}
