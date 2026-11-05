<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\Auth\Actions\StartAuthenticatedSession;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\PendingVerification;
use Symfony\Component\HttpFoundation\Response;

/**
 * Log in with a mobile number and the code sent to it, then hand over to javaabu/auth's
 * StartAuthenticatedSession (two-factor challenge, session, login response)
 */
class MobileLoginController extends VerificationFlowController
{
    protected function purpose(): Purposes
    {
        return Purposes::LOGIN;
    }

    protected function routeName(): string
    {
        return 'login.mobile';
    }

    public function create(Request $request): View|Responsable
    {
        $type = $this->userType($request);

        return $this->render($request, 'login', [
            'can_register' => MobileVerification::allowsRegistration($type) && $type->hasRoute('register.mobile'),
        ]);
    }

    /** @return array<string, mixed> */
    protected function pendingExtra(Request $request): array
    {
        return ['remember' => $request->boolean('remember')];
    }

    protected function verified(Request $request, MobileNumber $mobile_number): Response
    {
        $type = $this->userType($request);
        $pending = PendingVerification::get($request, $type, $this->purpose()) ?? [];

        PendingVerification::forget($request, $type, $this->purpose());

        $user = MobileVerification::userFor($type, $mobile_number);

        if (! $user) {
            return $this->expiredResponse($request);
        }

        return app(StartAuthenticatedSession::class)(
            $request,
            $type,
            $user,
            remember: (bool) ($pending['remember'] ?? false),
            skip_two_factor: MobileVerification::skipsTwoFactor(),
        );
    }

    protected function verifyTitle(): string
    {
        return __('Enter the code to log in');
    }
}
