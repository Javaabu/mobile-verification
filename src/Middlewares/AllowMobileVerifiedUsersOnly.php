<?php

namespace Javaabu\MobileVerification\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowMobileVerifiedUsersOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (empty($guards)) {
            $guards = [null];
        }

        if (! auth()->user()) {
            return $next($request);
        }

        foreach ($guards as $guard) {
            if (auth($guard)->check() && auth($guard)->user()->hasVerifiedMobile()) {
                return $next($request);
            }
        }

        return auth()->user()->redirectToMobileVerificationUrl();
    }
}
