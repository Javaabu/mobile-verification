<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Validation\Validator;
use Illuminate\View\View;
use Javaabu\SmsNotifications\Notifications\SmsNotification;

interface SendVerificationCodeContract extends HasRequestMobileNumberContract
{
    public function showVerificationCodeRequestForm(Request $request): View;

    public function requestVerificationCode(Request $request);

    public function getVerificationCodeRequestFormView(): ?string;

    public function sendSuccessfulVerificationCodeRequestResponse(MobileNumber $mobile_number, Request $request);

    public function sendFailedVerificationCodeRequestResponse(Request $request, ?Validator $validator = null);

    public function getVerificationCodeRequestValidator(Request $request): Validator;

    public function getVerificationCodeRequestValidationRules(Request $request): array;

    public function mustBeARegisteredMobileNumber(array $request_data): ?bool;

    // TODO: handle failed notification and redirect back with error message
    // implement an interface for all notifications and from listener throw an exception that will return back with error messages
    public function sendVerificationCodeSms(string $verification_code, MobileNumber $mobile_number): void;

    public function getVerificationCodeSmsNotification(string $verification_code, MobileNumber $mobile_number): SmsNotification;

    public function enableReCaptcha(): bool;

    public function redirectAfterVerificationCodeRequest(MobileNumber $mobile_number, Request $request);

    public function shouldAddMobileNumberToSession(): bool;
}
