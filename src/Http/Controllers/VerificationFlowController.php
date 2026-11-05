<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Actions\SendVerificationCode;
use Javaabu\MobileVerification\Actions\VerifyVerificationCode;
use Javaabu\MobileVerification\Enums\Purposes;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Support\PendingVerification;
use Symfony\Component\HttpFoundation\Response;

/**
 * The steps every flow shares: ask for a number, send it a code, show the code page,
 * check the code, and send another code. Subclasses say what happens once the code is right.
 */
abstract class VerificationFlowController extends Controller
{
    abstract protected function purpose(): Purposes;

    /**
     * The route names of the flow, without the type prefix: `login.mobile` → `login.mobile.verify`, …
     */
    abstract protected function routeName(): string;

    /**
     * What to do once the code is right
     */
    abstract protected function verified(Request $request, MobileNumber $mobile_number): Response;

    /** The layout the pages use: `auth` for guests, `account` for signed-in users */
    protected function layout(): string
    {
        return 'auth';
    }

    /** @return array<string, mixed> */
    protected function pendingExtra(Request $request): array
    {
        return [];
    }

    /**
     * Send a code to the number in the request
     */
    public function store(Request $request, SendVerificationCode $send): Response
    {
        $type = $this->userType($request);
        $input = $this->validateNumber($request);

        $mobile_number = $send($type, $this->purpose(), $input['country_code'], $input['number'], $this->user($request));

        PendingVerification::put($request, $type, $this->purpose(), $mobile_number, $this->pendingExtra($request));

        return $this->codeSentResponse($request, $mobile_number);
    }

    /**
     * The page to enter the code
     */
    public function verify(Request $request): View|Responsable|Response
    {
        $mobile_number = $this->pendingMobileNumber($request);

        if (! $mobile_number) {
            return redirect()->to($this->userType($request)->route($this->routeName()));
        }

        return $this->render($request, 'verify', [
            'purpose' => $this->purpose()->value,
            'title' => $this->verifyTitle(),
            'mobile_number' => $this->mobileNumberData($mobile_number),
            'verify_url' => $this->userType($request)->route("{$this->routeName()}.verify.store"),
            'resend_url' => $this->userType($request)->route("{$this->routeName()}.resend"),
            'change_url' => $this->userType($request)->route($this->routeName()),
        ], $this->layout());
    }

    /**
     * Check the code
     */
    public function confirm(Request $request, VerifyVerificationCode $verify): Response
    {
        $code = $this->validateCode($request);
        $mobile_number = $this->pendingMobileNumber($request);

        if (! $mobile_number) {
            return $this->expiredResponse($request);
        }

        $pending = PendingVerification::get($request, $this->userType($request), $this->purpose());

        $verify($mobile_number, $code, (string) ($pending['verification_code_id'] ?? ''));

        return $this->verified($request, $mobile_number);
    }

    /**
     * Send another code to the pending number
     */
    public function resend(Request $request, SendVerificationCode $send): Response
    {
        $type = $this->userType($request);
        $mobile_number = $this->pendingMobileNumber($request);

        if (! $mobile_number) {
            return $this->expiredResponse($request);
        }

        $pending = PendingVerification::get($request, $type, $this->purpose()) ?? [];

        $mobile_number = $send($type, $this->purpose(), $mobile_number->country_code, $mobile_number->number, $this->user($request), 'verification_code');

        PendingVerification::put($request, $type, $this->purpose(), $mobile_number, $pending);

        if ($request->expectsJson()) {
            return new JsonResponse($mobile_number->verificationCodeResponseData());
        }

        return back()->with('status', __('We sent a new code to :number.', ['number' => $mobile_number->formatted_number]));
    }

    protected function pendingMobileNumber(Request $request): ?MobileNumber
    {
        return PendingVerification::mobileNumber($request, $this->userType($request), $this->purpose());
    }

    protected function codeSentResponse(Request $request, MobileNumber $mobile_number): Response
    {
        if ($request->expectsJson()) {
            return new JsonResponse($mobile_number->verificationCodeResponseData());
        }

        return redirect()
            ->to($this->userType($request)->route("{$this->routeName()}.verify"))
            ->with('status', __('We sent a code to :number.', ['number' => $mobile_number->formatted_number]));
    }

    protected function expiredResponse(Request $request): Response
    {
        $message = __('Enter your mobile number again to get a new code.');

        if ($request->expectsJson()) {
            return new JsonResponse(['message' => $message, 'errors' => ['number' => [$message]]], 422);
        }

        return redirect()
            ->to($this->userType($request)->route($this->routeName()))
            ->withErrors(['number' => $message]);
    }

    protected function verifyTitle(): string
    {
        return __('Enter the code');
    }

    /** @return array<string, mixed> */
    protected function mobileNumberData(MobileNumber $mobile_number): array
    {
        return [
            'formatted_number' => $mobile_number->formatted_number,
            'expires_in' => $mobile_number->verificationCodeExpiresIn(),
            'resend_in' => $mobile_number->resendVerificationCodeIn(),
            'remaining_attempts' => $mobile_number->remainingAttempts(),
        ];
    }
}
