<?php

namespace Javaabu\MobileVerification\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signed-in users need a verified number: `mobile.verified:user`. Users without one go to their
 * type's mobile number page (Route::mobileNumberRoutes()), and come back once it's verified.
 * Guests pass through, so put it after `auth.type`.
 */
class EnsureMobileIsVerified
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $user_type = $type ? UserTypes::get($type) : (UserTypes::fromRequest($request) ?? UserTypes::default());
        $user = $user_type->auth()->user();

        if (! $user instanceof HasMobileNumber) {
            return $next($request);
        }

        if ($user->hasVerifiedMobile() || (method_exists($user, 'shouldVerifyPhone') && ! $user->shouldVerifyPhone())) {
            return $next($request);
        }

        if ($request->expectsJson() || ! $user_type->hasRoute('mobile-number')) {
            abort(403, __('Verify your mobile number to continue.'));
        }

        return redirect()->guest($user_type->route('mobile-number'))
            ->with('status', __('Verify your mobile number to continue.'));
    }
}
