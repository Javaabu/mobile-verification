<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\PendingVerification;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signed-in users verify a number, or change theirs, on their account settings
 */
class MobileNumberController extends VerificationFlowController
{
    protected function purpose(): Purposes
    {
        return Purposes::UPDATE;
    }

    protected function routeName(): string
    {
        return 'mobile-number';
    }

    protected function layout(): string
    {
        return 'account';
    }

    public function edit(Request $request): View|Responsable
    {
        $user = $this->user($request);
        $phone = $user instanceof HasMobileNumber ? $user->phone : null;

        return $this->render($request, 'settings.mobile-number', [
            'current_number' => $phone ? [
                'number' => $phone->number,
                'country_code' => $phone->country_code,
                'formatted_number' => $phone->formatted_number,
            ] : null,
        ], 'account');
    }

    protected function verified(Request $request, MobileNumber $mobile_number): Response
    {
        $type = $this->userType($request);
        $user = $this->user($request);

        PendingVerification::forget($request, $type, $this->purpose());

        if ($user instanceof HasMobileNumber) {
            $user->updatePhone($mobile_number, $user);
        }

        $message = __('Your mobile number is now :number.', ['number' => $mobile_number->formatted_number]);

        if ($request->expectsJson()) {
            return new JsonResponse(['verified' => true, 'message' => $message, 'formatted_number' => $mobile_number->formatted_number]);
        }

        return redirect()->intended($type->route('mobile-number'))->with('status', $message);
    }

    protected function verifyTitle(): string
    {
        return __('Enter the code to verify your number');
    }
}
