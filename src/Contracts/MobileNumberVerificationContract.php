<?php

namespace Javaabu\MobileVerification\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Javaabu\SmsNotifications\Notifiable\SmsNotifiable;

interface MobileNumberVerificationContract
{
    /**
     * Get the session key
     *
     * @return string
     */
    public function sessionKey(): string;

    /**
     * Get the session phone
     *
     * @param Request $request
     * @return ?MobileNumber
     */
    public function getSessionPhone(Request $request): ?MobileNumber;

    /**
     * Get the mobile number sms request view
     *
     * @param Request $request
     * @return string
     */
    public function getSmsRequestView(Request $request): string;

    /**
     * Show the sms request form
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showSmsRequestForm(Request $request): View;

    /**
     * Mobile number update form
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request);

    /**
     * Get the validation rules for the sms request
     */
    public function getSmsRequestValidationRules(string $country_code): array;

    /**
     * Validate sms request
     *
     * @param Request $request
     * @param string $country_code
     */
    public function validateSmsRequest(Request $request, string $country_code);

    /**
     * Check whether recaptcha should be verified
     *
     * @return boolean
     */
    public function enableRecaptcha(): bool;

    /**
     * Get the sms request phone
     *
     * @param Request $request
     * @param string $country_code
     * @return MobileNumber
     */
    public function getSmsRequestPhone(Request $request, string $country_code): MobileNumber;

    /**
     * Get the mobile number verification view
     *
     * @param Request $request
     * @return string
     */
    public function getVerificationView(Request $request): string;


}
