<?php

namespace Javaabu\MobileVerification;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Javaabu\Auth\UserTypes;
use Javaabu\Manifest\Concerns\RegistersAssets;
use Javaabu\Manifest\Package;
use Javaabu\MobileVerification\Actions\CreateUserFromMobileNumber;
use Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers;
use Javaabu\MobileVerification\Contracts\GeneratesVerificationCodes;
use Javaabu\MobileVerification\Contracts\ValidatesNumberFormat;
use Javaabu\MobileVerification\Http\Middleware\EnsureMobileIsVerified;
use Javaabu\MobileVerification\Passport\MobileGrant;
use Javaabu\MobileVerification\Policies\MobileNumberPolicy;
use Javaabu\MobileVerification\Routing\MobileApiRoutes;
use Javaabu\MobileVerification\Routing\MobileNumberActionRoutes;
use Javaabu\MobileVerification\Routing\MobileNumberRoutes;
use Javaabu\MobileVerification\Support\NumberFormatValidator;
use Javaabu\MobileVerification\Support\VerificationCodeGenerator;
use Javaabu\Permissions\Permissions;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;

class MobileVerificationServiceProvider extends ServiceProvider
{
    use RegistersAssets;

    /** Route middleware the package registers */
    public const array MIDDLEWARE = [
        'mobile.verified' => EnsureMobileIsVerified::class,
        // The v2 name
        'mobile-verified' => EnsureMobileIsVerified::class,
    ];

    /** Default implementations apps can replace */
    public const array BINDINGS = [
        CreatesUsersFromMobileNumbers::class => CreateUserFromMobileNumber::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mobile-verification.php', 'mobile-verification');

        foreach (self::BINDINGS as $contract => $implementation) {
            $this->app->bindIf($contract, $implementation);
        }

        // The classes in config, unless the app binds the contracts itself
        $this->app->bindIf(GeneratesVerificationCodes::class, fn () => $this->app->make(MobileVerification::config('code_generator') ?: VerificationCodeGenerator::class));
        $this->app->bindIf(ValidatesNumberFormat::class, fn () => $this->app->make(MobileVerification::config('number_format_validator') ?: NumberFormatValidator::class));

        $this->registerPassportGrant();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mobile-verification');

        if (MobileVerification::$runs_migrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->registerFrontendAssets();
        $this->registerMiddleware();
        $this->registerRouteMacros();
        $this->registerRateLimiters();
        $this->registerAbilities();
        $this->registerAccountLink();

        $this->app->booted(fn () => $this->registerPermissions());
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/mobile-verification.php' => config_path('mobile-verification.php'),
        ], 'mobile-verification-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/mobile-verification'),
        ], 'mobile-verification-views');

        $this->publishes([
            __DIR__ . '/../lang' => lang_path('vendor/mobile-verification'),
        ], 'mobile-verification-translations');

        $this->publishesMigrations([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'mobile-verification-migrations');
    }

    /**
     * The Inertia pages (mobile-verification::Login, …) and the code apps can import as
     * `@javaabu/mobile-verification`, through javaabu/manifest
     */
    protected function registerFrontendAssets(): void
    {
        $this->registerAssets('mobile-verification', __DIR__ . '/../resources', function (Package $package) {
            $package
                ->js()
                ->pages()
                ->npm([
                    '@javaabu/forms' => 'github:Javaabu/react-forms#next-gen',
                    '@javaabu/onu' => '^0.2.1',
                ], peer_dependencies: [
                    'react' => '^18.3 || ^19.0',
                    'react-dom' => '^18.3 || ^19.0',
                    '@inertiajs/react' => '^2.0 || ^3.0',
                ]);
        });
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        foreach (self::MIDDLEWARE as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('mobileNumberRoutes', function (?string $type = null, array $options = []) {
            MobileNumberRoutes::register($this, $type, $options);
        });

        Route::macro('mobileApiRoutes', function (?string $type = null, array $options = []) {
            MobileApiRoutes::register($this, $type, $options);
        });

        Route::macro('mobileNumberActions', function (string $type, array $options = []) {
            MobileNumberActionRoutes::register($this, $type, $options);
        });
    }

    /**
     * The `mobile` grant, when laravel/passport is installed
     */
    protected function registerPassportGrant(): void
    {
        if (! class_exists(Passport::class)) {
            return;
        }

        $this->app->afterResolving(AuthorizationServer::class, function (AuthorizationServer $server) {
            if (! MobileVerification::config('passport_grant', true)) {
                return;
            }

            $grant = new MobileGrant($this->app->make(RefreshTokenRepository::class));
            $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

            $server->enableGrantType($grant, Passport::tokensExpireIn());
        });
    }

    /**
     * The account page in javaabu/auth's account navigation, for every type with Route::mobileNumberRoutes()
     */
    protected function registerAccountLink(): void
    {
        UserTypes::accountLink('mobile-number', 'Mobile number', position: 30);
    }

    /**
     * The abilities of MobileNumberPolicy (or mobile-verification.policy), unless the app defined them
     */
    protected function registerAbilities(): void
    {
        $policy = MobileVerification::config('policy', MobileNumberPolicy::class);

        foreach (MobileNumberPolicy::ABILITIES as $ability => $action) {
            if (Gate::has($ability)) {
                continue;
            }

            Gate::define($ability, [$policy, $action]);
        }
    }

    /**
     * Permissions for managing each user type's numbers, through javaabu/permissions
     */
    protected function registerPermissions(): void
    {
        if (! MobileVerification::config('register_permissions', true)) {
            return;
        }

        $managed = MobileVerification::config('managed_user_types') ?: array_keys(UserTypes::all());

        foreach ($managed as $name) {
            $type = UserTypes::get($name);
            $group = MobileNumberPolicy::group($type);
            $permissions = [];

            foreach (MobileNumberPolicy::DESCRIPTIONS as $action => $description) {
                // English, as javaabu/permissions translates descriptions when it shows them
                $permissions[MobileNumberPolicy::permission($action, $type)] = strtr($description, [':resource' => str_replace('_', ' ', $group)]);
            }

            Permissions::register($group, $permissions);
        }
    }

    protected function registerRateLimiters(): void
    {
        $limit = fn (string $key, int $default) => (int) MobileVerification::config("rate_limits.{$key}", $default);
        $type = fn (Request $request) => UserTypes::fromRequest($request)?->name() ?? 'default';

        RateLimiter::for('mobile-verification.send', function (Request $request) use ($limit, $type) {
            return Limit::perMinute($limit('send', 5))->by("{$type($request)}|{$request->ip()}");
        });

        RateLimiter::for('mobile-verification.verify', function (Request $request) use ($limit, $type) {
            $session = $request->hasSession() ? $request->session()->getId() : '';

            return Limit::perMinute($limit('verify', 10))->by("{$type($request)}|{$request->ip()}|{$session}");
        });
    }
}
