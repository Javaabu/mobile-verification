<?php

namespace Javaabu\MobileVerification\Routing;

use Illuminate\Routing\Router;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\MobileVerification;

/**
 * Token endpoints for API clients: `Route::mobileApiRoutes('user', ['prefix' => 'api'])`
 * registers `POST {prefix}/mobile/code` and `POST {prefix}/mobile/token`
 */
class MobileApiRoutes
{
    /**
     * @param  array{
     *     prefix?: string,
     *     middleware?: string|array<int, string>
     * }  $options
     */
    public static function register(Router $router, ?string $type = null, array $options = []): void
    {
        $user_type = $type ? UserTypes::get($type) : UserTypes::default();
        $controller = MobileVerification::config('controllers.api');

        $router->group([
            'prefix' => $options['prefix'] ?? 'api',
            'as' => "{$user_type->routeNamePrefix()}api.mobile.",
            'middleware' => (array) ($options['middleware'] ?? ['api']),
            UserTypeRegistry::ROUTE_ACTION => $user_type->name(),
        ], function (Router $router) use ($controller) {
            $router->post('mobile/code', [$controller, 'code'])->middleware('throttle:mobile-verification.send')->name('code');
            $router->post('mobile/token', [$controller, 'token'])->middleware('throttle:mobile-verification.verify')->name('token');
        });
    }
}
