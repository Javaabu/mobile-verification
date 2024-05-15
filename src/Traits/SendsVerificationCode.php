<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Javaabu\MobileVerification\MobileVerification;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Contracts\SendVerificationCodeContract;

/* @var SendVerificationCodeContract $this */
trait SendsVerificationCode
{

    public function showVerificationCodeRequestForm(): View
    {
        return view($this->getVerificationCodeRequestFormView());
    }

    public function requestVerificationCode(Request $request)
    {
        $validator = $this->getVerificationCodeRequestValidator($request);

        if ($validator->fails()) {
            return $this->sendFailedCodeVerificationResponse($request, $validator);
        }

        $mobile_number = $this->getMobileNumberFromRequest($request);
        $verification_code = $mobile_number->generateVerificationCode();

        $this->sendVerificationCodeSms($verification_code, $mobile_number);

        if (method_exists($this, 'getSessionMobileNumberKey') && $this->getSessionMobileNumberKey()) {
            $this->setSessionMobileNumber($mobile_number, $request);
        }

        return $this->sendSuccessfulVerificationCodeRequestResponse(
            $mobile_number,
            $request
        );
    }

    public function sendSuccessfulVerificationCodeRequestResponse(MobileNumber $mobile_number, Request $request)
    {
        if (expects_json($request)) {
            return response()->json($mobile_number->verificationCodeResponseData());
        }

        return back()->with([
            'success'       => true,
            'mobile_number' => $mobile_number,
        ]);
    }

    public function sendFailedVerificationCodeRequestResponse(Request $request, ?Validator $validator = null)
    {
        if (expects_json($request)) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        return back()->withErrors($validator);
    }

    public function getVerificationCodeRequestValidator(Request $request): Validator
    {
        return ValidatorFacade::make($request->all(), $this->getVerificationCodeRequestValidationRules($request));
    }

    public function getVerificationCodeRequestValidationRules(Request $request): array
    {
        $valid_mobile_number_rule = (new IsValidMobileNumber(
            $this->getUserType(),
            $this->getCountryCodeInputKey()
        ))
            ->canSendOtp()
            ->setShouldBeRegisteredNumber($this->mustBeARegisteredMobileNumber($request->all()));

        $rules = [
            $this->getMobileNumberInputKey() => ['required', 'string', $valid_mobile_number_rule],
            $this->getCountryCodeInputKey()  => ['nullable', 'string', new IsValidCountryCode()],
        ];

        if ($this->enableReCaptcha()) {
            $rules[recaptchaFieldName()] = recaptchaRuleName();
        }

        return $rules;
    }

    public function sendVerificationCodeSms(string $verification_code, MobileNumber $mobile_number): void
    {
        $mobile_number->notify($this->getVerificationCodeSmsNotification($verification_code, $mobile_number));
    }

    public function enableReCaptcha(): bool
    {
        return config('mobile-verification.use_recaptcha');
    }
}
