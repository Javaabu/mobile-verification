<?php

namespace Javaabu\MobileVerification\Auth;

use Illuminate\Routing\Router;
use Javaabu\Auth\Contracts\LoginMethod;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\MobileVerification;

/**
 * Log in (and sign up) with a mobile number and a code by SMS. Add it to a javaabu/auth user type:
 * `'login_methods' => [MobileLoginMethod::class]`, or `MobileVerification::enableFor('user')`.
 * Route::authRoutes() then registers the routes in the type's guest group.
 */
class MobileLoginMethod implements LoginMethod
{
    public const string KEY = 'mobile';

    public function key(): string
    {
        return self::KEY;
    }

    public function label(): string
    {
        return __('Log in with your mobile number');
    }

    public function routes(Router $router, UserType $type): void
    {
        $login = MobileVerification::config('controllers.login');

        $router->get('login/mobile', [$login, 'create'])->name('login.mobile');
        $router->post('login/mobile', [$login, 'store'])->middleware('throttle:mobile-verification.send')->name('login.mobile.store');
        $router->get('login/mobile/verify', [$login, 'verify'])->name('login.mobile.verify');
        $router->post('login/mobile/verify', [$login, 'confirm'])->middleware('throttle:mobile-verification.verify')->name('login.mobile.verify.store');
        $router->post('login/mobile/resend', [$login, 'resend'])->middleware('throttle:mobile-verification.send')->name('login.mobile.resend');

        if (! MobileVerification::allowsRegistration($type)) {
            return;
        }

        $register = MobileVerification::config('controllers.register');

        $router->get('register/mobile', [$register, 'create'])->name('register.mobile');
        $router->post('register/mobile', [$register, 'store'])->middleware('throttle:mobile-verification.send')->name('register.mobile.store');
        $router->get('register/mobile/verify', [$register, 'verify'])->name('register.mobile.verify');
        $router->post('register/mobile/verify', [$register, 'confirm'])->middleware('throttle:mobile-verification.verify')->name('register.mobile.verify.store');
        $router->post('register/mobile/resend', [$register, 'resend'])->middleware('throttle:mobile-verification.send')->name('register.mobile.resend');
        $router->get('register/mobile/details', [$register, 'details'])->name('register.mobile.details');
        $router->post('register/mobile/details', [$register, 'complete'])->name('register.mobile.details.store');
    }

    public function url(UserType $type): string
    {
        return $type->route('login.mobile');
    }
}
