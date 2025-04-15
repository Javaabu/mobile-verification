<?php

namespace Javaabu\MobileVerification;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Javaabu\MobileVerification\GrantType\MobileGrant;
use Javaabu\MobileVerification\Support\VerificationCodeGenerator;
use Javaabu\MobileVerification\Middlewares\AllowMobileVerifiedUsersOnly;

class MobileVerificationServiceProvider extends ServiceProvider
{
    /**
     * The package migrations, in order of creation.
     *
     * @var array|string[]
     */
    protected array $migrations = [
        'create_mobile_numbers_table'
    ];

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            // Publish migrations with current timestamp
            foreach ($this->migrations as $migration) {
                $vendorMigration = __DIR__ . '/../database/migrations/' . $migration . '.php';
                $appMigration = $this->generateMigrationName($migration, now()->addSecond());

                $this->publishes([
                    $vendorMigration => $appMigration,
                ], 'mobile-verification-migrations');
            }

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

        $this->app->bind(VerificationCodeGenerator::class, function () {
            return new VerificationCodeGenerator();
        });
    }

    protected function makeGrant(): MobileGrant
    {
        $grant = new MobileGrant(
            $this->app->make(RefreshTokenRepository::class)
        );

        $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        return $grant;
    }

    protected function generateMigrationName(string $migrationFileName, Carbon $now): string
    {
        $migrationsPath = 'migrations/' . dirname($migrationFileName) . '/';
        $migrationFileName = basename($migrationFileName);

        $len = strlen($migrationFileName) + 4;

        if (Str::contains($migrationFileName, '/')) {
            $migrationsPath .= Str::of($migrationFileName)->beforeLast('/')->finish('/');
            $migrationFileName = Str::of($migrationFileName)->afterLast('/');
        }

        foreach (glob(database_path("{$migrationsPath}*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName . '.php')) {
                return $filename;
            }
        }

        $timestamp = $now->format('Y_m_d_His');
        $migrationFileName = Str::of($migrationFileName)->snake()->finish('.php');

        return database_path($migrationsPath . $timestamp . '_' . $migrationFileName);
    }
}
