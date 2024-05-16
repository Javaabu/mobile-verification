<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

interface VerifyVerificationCodeContract extends HasRequestMobileNumberContract
{
    public function showVerificationCodeForm(Request $request, ?MobileNumber $mobile_number = null): View;

    public function getVerificationCodeFormView(): ?string;

    public function verifyVerificationCode(Request $request);

    public function doAfterVerificationCodeVerified(MobileNumber $mobile_number, Request $request): mixed;

    public function sendSuccessfulCodeVerificationResponse(MobileNumber $mobile_number, Request $request, $data = null);

    public function sendFailedCodeVerificationResponse(Request $request, ?Validator $validator = null, ?MobileNumber $mobile_number = null);

    public function getVerificationCodeValidator(Request $request): Validator;

    public function getVerificationCodeValidationRules(Request $request): array;

    public function shouldResetVerificationAttemptsOnSuccess(): bool;

    public function verificationCodeSuccessRedirectUrl(): string;

    public function getVerificationCodeSuccessMessage(MobileNumber $mobile_number, Request $request, $data = null): string;

    public function flashVerificationCodeSuccessMessage(MobileNumber $mobile_number, Request $request, $data = null): void;

    public function getVerificationCodeSuccessMessageTitle(MobileNumber $mobile_number, Request $request, $data = null): string;

    public function redirectAfterSuccessfulCodeVerification(MobileNumber $mobile_number, Request $request, $data = null);

    public function getVerifiedCodeDataSessionKey(): string;
}
