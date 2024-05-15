<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

trait RegistersWithMobileNumber
{
    use HasRequestMobileNumber;
    use HasUserType;
    use SendsVerificationCode;
    use UsesSessionMobileNumber;
    use VerifiesVerificationCode;
    use RegistersUsers, UsesGuard {
        UsesGuard::guard insteadof RegistersUsers;
    }


    protected function validator(array $data)
    {
        $rules = $this->getRegistrationVerificationCodeValidationRules(request());

        return Validator::make($data, array_merge($rules, $this->getRegisterFieldsValidationRules(request())));
    }

    public function getRegistrationVerificationCodeValidationRules(Request $request): array
    {
        return $this->getVerificationCodeValidationRules(request());
    }

    public function create(array $data)
    {
        $user = $this->createUser($data);

        $mobile_number = $this->getMobileNumberFromRequest(request());

        $user->updatePhone($mobile_number);

        return $user;
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
        //
    }

    public function getVerificationCodeSmsNotification(string $verification_code, MobileNumber $mobile_number): SmsNotification
    {
        $notification_class = config('mobile-verification.notifications.register');

        return new $notification_class($verification_code, $mobile_number);
    }

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return false;
    }

    public function getSessionMobileNumberKey(): string
    {
        return 'mobile_number_to_register';
    }

    public function shouldAddMobileNumberToSession(): bool
    {
        return true;
    }

    public function shouldResetVerificationAttemptsOnSuccess(): bool
    {
        return false;
    }

    public function applyMiddlewares(): void
    {
        $this->middleware('guest:' . $this->getGuardName());
    }

    public function getGuard(): StatefulGuard
    {
        return $this->guard();
    }

    public function determinePathForRedirectUsing(): \Javaabu\Auth\User
    {
        return (new ($this->getUserClass()));
    }

    public function userClass(): string
    {
        return $this->getUserClass();
    }

    public function redirectPath()
    {
        return with($this->determinePathForRedirectUsing())->homeUrl();
    }
}
