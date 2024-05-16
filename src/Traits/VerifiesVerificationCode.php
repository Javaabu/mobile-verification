<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;
use Illuminate\View\View;
use Javaabu\MobileVerification\Contracts\MobileNumber;
use Javaabu\MobileVerification\Contracts\VerifyVerificationCodeContract;
use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;
use Javaabu\MobileVerification\Rules\IsValidVerificationCode;

/* @var VerifyVerificationCodeContract $this */
trait VerifiesVerificationCode
{
    public function verifyVerificationCode(Request $request)
    {
        $validator = $this->getVerificationCodeValidator($request);
        $mobile_number = $this->getMobileNumberFromRequest($request);

        if ($validator->fails()) {
            return $this->sendFailedCodeVerificationResponse($request, $validator, $mobile_number);
        }

        $data = $this->doAfterVerificationCodeVerified($mobile_number, $request);

        return $this->sendSuccessfulCodeVerificationResponse($mobile_number, $request, $data);
    }

    public function showVerificationCodeForm(Request $request, ?MobileNumber $mobile_number = null): View
    {
        return view($this->getVerificationCodeFormView(), [
            'mobile_number' => $mobile_number,
        ]);
    }

    public function sendSuccessfulCodeVerificationResponse(MobileNumber $mobile_number, Request $request, $data = null)
    {
        if (expects_json($request)) {
            return response()->json([
                'verified' => true,
                'mobile_number' => $mobile_number->verificationCodeResponseData(),
            ]);
        }

        $this->flashVerificationCodeSuccessMessage($mobile_number, $request, $data);

        return $this->redirectAfterSuccessfulCodeVerification($mobile_number, $request, $data);
    }

    public function redirectAfterSuccessfulCodeVerification(MobileNumber $mobile_number, Request $request, $data = null)
    {
        return redirect()
            ->to($this->verificationCodeSuccessRedirectUrl())
            ->with([
                'success' => true,
                'mobile_number' => $mobile_number,
                'data' => $data
            ]);
    }

    public function flashVerificationCodeSuccessMessage(MobileNumber $mobile_number, Request $request, $data = null): void
    {
        flash_push('alerts', [
            'text' => $this->getVerificationCodeSuccessMessage($mobile_number, $request, $data),
            'type' => 'success',
            'title' => $this->getVerificationCodeSuccessMessageTitle($mobile_number, $request, $data),
        ]);
    }

    public function getVerificationCodeSuccessMessage(MobileNumber $mobile_number, Request $request, $data = null): string
    {
        return trans('mobile-verification::messages.verification_code_verified');
    }

    public function getVerificationCodeSuccessMessageTitle(MobileNumber $mobile_number, Request $request, $data = null): string
    {
        return trans('mobile-verification::messages.verification_code_verified_title');
    }

    public function sendFailedCodeVerificationResponse(Request $request, ?Validator $validator = null, ?MobileNumber $mobile_number = null)
    {
        if (expects_json($request)) {
            return response()->json([
                'errors' => $validator->errors(),
                'mobile_number' => $mobile_number?->verificationCodeResponseData(),
            ], 422);
        }

        if ($this->getVerificationCodeFormView()) {
            return $this->showVerificationCodeForm($mobile_number)->withErrors($validator);
        }

        return back()->withErrors($validator);
    }

    public function getVerificationCodeValidator(Request $request): Validator
    {
        return ValidatorFacade::make($request->all(), $this->getVerificationCodeValidationRules($request));
    }

    public function getVerificationCodeValidationRules(Request $request): array
    {
        $valid_mobile_number_rule = (new IsValidMobileNumber(
            $this->getUserType(),
            $this->getCountryCodeInputKey()
        ))
            ->canSendOtp()
            ->setShouldBeRegisteredNumber($this->mustBeARegisteredMobileNumber($request->all()));

        $valid_verification_code_rule = (new IsValidVerificationCode(
            $this->getUserType(),
            $this->getCountryCodeInputKey(),
            $this->getMobileNumberInputKey(),
            $this->getVerificationCodeIdInputKey()
        ))
            ->setShouldResetAttempts($this->shouldResetVerificationAttemptsOnSuccess());

        $rules = [
            $this->getMobileNumberInputKey() => ['required', 'string', $valid_mobile_number_rule],
            $this->getCountryCodeInputKey() => ['nullable', 'string', new IsValidCountryCode()],
            $this->getVerificationCodeInputKey() => ['required', 'string', $valid_verification_code_rule],
            $this->getVerificationCodeIdInputKey() => ['required', 'string'],
        ];

        return $rules;
    }

    public function shouldResetVerificationAttemptsOnSuccess(): bool
    {
        return true;
    }
}
