<?php

namespace Javaabu\MobileVerification\Routing;

use Illuminate\Routing\Router;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Policies\MobileNumberPolicy;

/**
 * Admin actions on one user type's numbers for `Route::mobileNumberActions('user', [...])`.
 * Put them behind the admins' middleware, e.g. `['web', 'auth.type:admin']`.
 */
class MobileNumberActionRoutes
{
    /** The route action key that holds the managed user type */
    public const string ROUTE_ACTION = 'mobile_verification_managed_user_type';

    /**
     * @param  array{
     *     prefix?: string,
     *     name?: string,
     *     middleware?: string|array<int, string>
     * }  $options
     */
    public static function register(Router $router, string $type, array $options = []): void
    {
        $user_type = UserTypes::get($type);
        $group = MobileNumberPolicy::group($user_type);
        $controller = MobileVerification::config('controllers.mobile_number_actions');
        $name = rtrim($options['name'] ?? "{$group}.mobile-number", '.');

        $router->group([
            'prefix' => $options['prefix'] ?? $group,
            'as' => "{$name}.",
            'middleware' => (array) ($options['middleware'] ?? ['web']),
            self::ROUTE_ACTION => $user_type->name(),
        ], function (Router $router) use ($controller) {
            $router->put('{account}/mobile-number', [$controller, 'update'])->name('update');
            $router->post('{account}/mobile-number/unlock', [$controller, 'unlock'])->name('unlock');
            $router->delete('{account}/mobile-number', [$controller, 'destroy'])->name('destroy');
        });
    }
}
