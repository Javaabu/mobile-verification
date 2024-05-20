<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

trait UpdatesMobileNumber
{
    use HasRequestMobileNumber;
    use HasUserType;
    use SendsVerificationCode;
    use UsesSessionMobileNumber;
    use VerifiesVerificationCode;

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
        $user = $request->user();
        $user->updatePhone($mobile_number);

        return $user;
    }

    public function getVerificationCodeSmsNotification(string $verification_code, MobileNumber $mobile_number): SmsNotification
    {
        $notification_class = config('mobile-verification.notifications.update');

        return new $notification_class($verification_code, $mobile_number);
    }

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return false;
    }

    public function getSessionMobileNumberKey(): string
    {
        return 'mobile_to_update';
    }

    public function shouldAddMobileNumberToSession(): bool
    {
        return true;
    }

    public function getVerificationCodeSuccessMessage(MobileNumber $mobile_number, Request $request, $data = null): string
    {
        return trans('mobile-verification::messages.mobile_number_updated', [
            'mobile_number' => $mobile_number->formatted_number,
        ]);
    }

    public function getVerificationCodeSuccessMessageTitle(MobileNumber $mobile_number, Request $request, $data = null): string
    {
        return trans('mobile-verification::messages.mobile_number_updated_title');
    }

    public function redirectAfterVerificationCodeRequest(MobileNumber $mobile_number, Request $request): \Illuminate\View\View
    {
        return $this->showVerificationCodeForm($request, $mobile_number);
    }
}
