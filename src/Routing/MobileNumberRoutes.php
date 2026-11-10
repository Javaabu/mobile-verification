<?php

namespace Javaabu\MobileVerification\Routing;

use Illuminate\Routing\Router;
use Javaabu\Auth\UserTypeRegistry;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\MobileVerification;

/**
 * The account page where signed-in users verify or change their number:
 * `Route::mobileNumberRoutes('user', ['prefix' => 'settings'])`
 */
class MobileNumberRoutes
{
    /**
     * @param  array{
     *     prefix?: string,
     *     middleware?: string|array<int, string>,
     *     domain?: string|null,
     *     confirm_password?: bool
     * }  $options
     */
    public static function register(Router $router, ?string $type = null, array $options = []): void
    {
        $user_type = $type ? UserTypes::get($type) : UserTypes::default();
        $controller = MobileVerification::config('controllers.mobile_number');

        $middleware = [
            ...(array) ($options['middleware'] ?? ['web']),
            "auth.type:{$user_type->name()}",
        ];

        if ($options['confirm_password'] ?? false) {
            $middleware[] = "auth.password.confirm:{$user_type->name()}";
        }

        $attributes = [
            'prefix' => $options['prefix'] ?? 'settings',
            'as' => $user_type->routeNamePrefix(),
            'middleware' => $middleware,
            UserTypeRegistry::ROUTE_ACTION => $user_type->name(),
        ];

        if (! empty($options['domain'])) {
            $attributes['domain'] = $options['domain'];
        }

        $router->group($attributes, function (Router $router) use ($controller) {
            $router->get('mobile-number', [$controller, 'edit'])->name('mobile-number');
            $router->post('mobile-number', [$controller, 'store'])->middleware('throttle:mobile-verification.send')->name('mobile-number.store');
            $router->get('mobile-number/verify', [$controller, 'verify'])->name('mobile-number.verify');
            $router->post('mobile-number/verify', [$controller, 'confirm'])->middleware('throttle:mobile-verification.verify')->name('mobile-number.verify.store');
            $router->post('mobile-number/resend', [$controller, 'resend'])->middleware('throttle:mobile-verification.send')->name('mobile-number.resend');
        });
    }
}
