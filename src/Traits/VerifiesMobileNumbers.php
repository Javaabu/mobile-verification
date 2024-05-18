<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Javaabu\MobileVerification\MobileVerification;
use Javaabu\MobileVerification\Contracts\MobileNumber;

trait VerifiesMobileNumbers
{
    use HasUserType;
    use RedirectsUsers;




    /**
     * Get the session phone
     *
     * @param Request $request
     * @return ?MobileNumber
     */
    public function getSessionPhone(Request $request): ?MobileNumber
    {
        if ($request->session()->has($this->sessionKey())) {
            $phone_id = $request->session()->pull($this->sessionKey());

            $model = MobileVerification::mobileNumberModel();

            $phone = $model::find($phone_id);

            return $phone;
        }

        return null;
    }

    /**
     * Show the sms request form
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showSmsRequestForm(Request $request): View
    {
        $user = $request->user();

        return view($this->getSmsRequestView($request), compact('user'));
    }

    /**
    * Mobile number update form
    *
    * @param Request $request
    * @return \Illuminate\Http\Response
    */
    public function create(Request $request)
    {
        // first check if a the session has a phone
        if ($phone = $this->getSessionPhone($request)) {
            return $this->store($request, $phone);
        }

        return $this->showSmsRequestForm($request);
    }

    /**
     * Get the validation rules for the sms request
     */
    public function getSmsRequestValidationRules(string $country_code): array
    {
        $rules = [
            'phone' => [
                'required',
                'mobile:'.$country_code,
                Rule::unique('mobile_numbers', 'number')->where(function ($query) use ($country_code) {
                    $query->whereNotNull('user_id')
                        ->whereUserType($this->userTypeString())
                        ->whereCountryCode($country_code);
                }),
            ],
            'country_code' => [
                'required',
                Rule::in(MobileNumber::allowedCountryCodes()),
            ],
        ];

        if ($this->enableRecaptcha()) {
            $rules[recaptchaFieldName()] = recaptchaRuleName();
        }

        return $rules;
    }

    /**
     * Validate sms request
     *
     * @param Request $request
     * @param string $country_code
     */
    public function validateSmsRequest(Request $request, string $country_code)
    {
        $rules = $this->getSmsRequestValidationRules($country_code);

        $this->validate($request, $rules);
    }

    /**
     * Check whether recaptcha should be verified
     *
     * @return bool
     */
    public function enableRecaptcha(): bool
    {
        return property_exists($this, 'enable_recaptcha') ? $this->enable_recaptcha : config('mobile-verification.use_recaptcha');
    }

    /**
     * Get the sms request phone
     *
     * @param Request $request
     * @param string $country_code
     * @return MobileNumber
     */
    public function getSmsRequestPhone(Request $request, string $country_code): MobileNumber
    {
        $model = MobileVerification::mobileNumberModel();

        return $model::firstOrCreate([
            'country_code' => $country_code,
            'number' => $request->input('phone'),
            'user_type' => $this->userTypeString(),
        ]);
    }

    /**
     * Get the verification phone
     *
     * @param Request $request
     * @return MobileNumber
     */
    protected function getVerificationPhone(Request $request)
    {
        $phone_id = $request->phone_id;

        return MobileNumber::whereId($phone_id)
            ->whereNull('user_id')
            ->whereUserType($this->userTypeString())
            ->first();
    }

    /**
     * Send the code
     * @param string $code
     * @param MobileNumber $phone
     */
    protected function sendSmsNotification($code, $phone)
    {
        $user_name = $phone->user ? $phone->user->name : '';

        $phone->notify(new MobileNumberVerificationToken($code, $user_name));
    }

    /**
     * Return show verification response
     *
     * @param Request $request
     * @param MobileNumber $phone
     * @return Response
     */
    protected function showVerificationForm(Request $request, MobileNumber $phone)
    {
        if (expects_json($request)) {
            return response()->json($phone);
        }

        return view($this->getVerificationView($request), compact('phone'));
    }

    /**
     * Store the specified resource in storage.
     *
     * @param Request $request
     * @param MobileNumber $phone
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, MobileNumber $phone = null)
    {
        $request = MobileNumber::normalizePhoneNumberRequest($request);
        $country_code = $request->input('country_code', MobileNumber::defaultCountryCode());

        //create the number
        if (empty($phone)) {
            //first validate
            $this->validateSmsRequest($request, $country_code);
            $phone = $this->getSmsRequestPhone($request, $country_code);
        }

        // directly assign if not verifying
        if (! MobileNumber::shouldVerify()) {
            $user = $request->user();
            $this->assignPhone($phone, $user);

            return $this->returnMobileNumberUpdatedResponse($request, $phone);
        }

        //check if locked
        if (! $phone->can_request_code) {
            throw new MobileNumberException(__(
                'Too many verification attempts. Request for a new verification code in :min minutes.',
                ['min' => $phone->attempts_expiry]
            ));
        }

        // check if the verification_code was sent recently
        if ($phone->was_sent_recently) {
            throw new MobileNumberException(
                __('A verification code was sent to this number too recently. '.
                'Please wait a few moments before resending.')
            );
        }

        //generate the verification_code
        $verification_code = $phone->generateVerificationCode();

        //send the verification_code to the user
        $this->sendSmsNotification($verification_code, $phone);

        return $this->showVerificationForm($request, $phone);
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'phone_id' => [
                'required',
                Rule::exists('mobile_numbers', 'id')->where(function ($query) {
                    $query->whereNull('user_id')
                        ->whereUserType($this->userTypeString());
                }),
            ],

            'code' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Redirect to the sms request page
     *
     * @return Response
     */
    protected function redirectToSmsRequestForm()
    {
        return redirect()->action([self::class, 'create']);
    }

    /**
     * Assign the phone
     *
     * @param MobileNumber $phone
     * @param PhoneVerifiableContract $user
     */
    protected function assignPhone(MobileNumber $phone, PhoneVerifiableContract $user)
    {
        //assign the phone to the user
        $user->updatePhone($phone->id, $user);
    }

    /**
     * Update the mobile number of the user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();

        //first validate
        $validator = Validator::make($request->all(), $this->rules());

        // find the number
        $phone = $this->getVerificationPhone($request);

        // redirect to mobile number page if no phone id
        if (! $request->phone_id) {
            if (expects_json($request)) {
                throw new MobileNumberException(__('The phone id is invalid or not available.'));
            }

            return $this->redirectToSmsRequestForm();
        }

        // validate the inputs
        if ($validator->fails()) {
            return $this->returnFailedVerificationResponse($request, $validator, $phone);
        }

        try {
            //check if locked
            if ($phone->is_locked) {
                throw new MobileNumberException(__(
                    'Too many verification attempts. Request for a new verification code in :min minutes.',
                    ['min' => $phone->attempts_expiry]
                ));
            }

            //check if the verification_code is expired
            if ($phone->is_verification_code_expired) {
                throw new MobileNumberException(__('The verification code for this number is expired.'));
            }

            //verify the code
            if ($phone->verifyVerificationCode($request->code)) {
                $this->assignPhone($phone, $user);
            } else {
                throw new MobileNumberException('The code is invalid.');
            }
        } catch (MobileNumberException $e) {
            return $this->returnFailedVerificationResponse(
                $request,
                ['mobile_number' => __($e->getMessage())],
                $phone
            );
        }

        return $this->returnMobileNumberUpdatedResponse($request, $phone);
    }

    /**
     * Return show verification response
     * @param Request $request
     * @param array $errors
     * @param MobileNumber $phone
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|Response|\Illuminate\View\View
     */
    protected function returnFailedVerificationResponse(Request $request, $errors = [], MobileNumber $phone = null)
    {
        if (expects_json($request)) {
            return response()->json([
                'message' => __('Mobile number verification failed'),
                'errors' => is_array($errors) ? $errors : $errors->messages(),
            ], 422);
        }

        return view($this->getVerificationView($request), compact('phone'))
            ->withErrors($errors);
    }

    /**
     * Send mobile number update request
     * @param Request $request
     * @param MobileNumber $phone
     * @return Response
     */
    public function returnMobileNumberUpdatedResponse(Request $request, MobileNumber $phone)
    {
        if (expects_json($request)) {
            return response()->json($phone);
        }

        flash_push('alerts', [
            'text' => __('Your mobile number has been updated to :number', [
                'number' => $phone->formatted_number,
            ]),
            'type' => 'success',
            'title' => __('Mobile Number Updated'),
        ]);

        return redirect($this->redirectPath());
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        $user = request()->user();

        return $user->phoneVerificationRedirectUrl();
    }
}
