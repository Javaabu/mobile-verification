<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

trait LoginsWithMobileNumber
{
    use HasRequestMobileNumber;
    use HasUserType;
    use SendsVerificationCode;
    use UsesGuard;
    use UsesSessionMobileNumber;
    use VerifiesVerificationCode;

    public function __construct()
    {
        $this->applyMiddlewares();
    }

    public function applyMiddlewares(): void
    {
        $this->middleware('guest:' . $this->getGuardName());
    }

    public function showVerificationCodeRequestForm(Request $request): View
    {
        if ($mobile_number = $this->getSessionMobileNumber($request)) {
            return $this->showVerificationCodeForm($request, $mobile_number);
        }

        return view($this->getVerificationCodeRequestFormView());
    }

    public function doAfterVerificationCodeVerified(MobileNumber $mobile_number, Request $request): mixed
    {
        /* @var HasMobileNumber $user */
        $user = $mobile_number->user;

        $this->guard()->login($user);
        $request->session()->regenerate();

        return $user;
    }

    public function flashVerificationCodeSuccessMessage(MobileNumber $mobile_number, Request $request, $data = null): void
    {
        //
    }

    public function getVerificationCodeSmsNotification(string $verification_code, MobileNumber $mobile_number): SmsNotification
    {
        $notification_class = config('mobile-verification.notifications.login');

        return new $notification_class($verification_code, $mobile_number?->user?->name);
    }

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return true;
    }

    public function getSessionMobileNumberKey(): string
    {
        return 'mobile_number_to_login';
    }

    public function shouldAddMobileNumberToSession(): bool
    {
        return true;
    }
}
