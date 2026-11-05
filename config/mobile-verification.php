<?php

use Javaabu\MobileVerification\Http\Controllers;
use Javaabu\MobileVerification\Models\MobileNumber;
use Javaabu\MobileVerification\Notifications\VerificationCodeNotification;
use Javaabu\MobileVerification\Policies\MobileNumberPolicy;
use Javaabu\MobileVerification\Stencil\MobileRegistrationForm;
use Javaabu\MobileVerification\Support\NumberFormatValidator;
use Javaabu\MobileVerification\Support\VerificationCodeGenerator;

return [

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Which stack the pages render with: `blade` (javaabu/forms) or `inertia`
    | (@javaabu/forms React pages shipped through javaabu/manifest). Null
    | follows the javaabu/auth user type, so the mobile pages match the rest
    | of its login pages. Requests that expect JSON always get JSON.
    |
    */

    'views' => env('MOBILE_VERIFICATION_VIEWS'),

    /*
    |--------------------------------------------------------------------------
    | Mobile numbers table
    |--------------------------------------------------------------------------
    |
    | The connection and table of the mobile numbers. The connection defaults
    | to database.default. The model must extend the package's model.
    |
    */

    'database_connection' => env('MOBILE_NUMBER_DB_CONNECTION'),

    'table_name' => 'mobile_numbers',

    'mobile_number_model' => MobileNumber::class,

    /*
    |--------------------------------------------------------------------------
    | Country codes
    |--------------------------------------------------------------------------
    |
    | The country code numbers get when none is given, the ones users can
    | choose from, and what goes before the country code when a number is
    | shown (+960 7771234).
    |
    */

    'default_country_code' => env('MOBILE_NUMBER_DEFAULT_COUNTRY_CODE', '960'),

    'allowed_country_codes' => explode(',', env('MOBILE_NUMBER_ALLOWED_COUNTRY_CODES', '960')),

    'number_prefix' => '+',

    /*
    | Checks a number's format for its country code. The default accepts 7
    | digit Maldivian mobile numbers starting with 7 or 9, and any number of
    | 4 to 15 digits for other country codes. Implements
    | Javaabu\MobileVerification\Contracts\ValidatesNumberFormat.
    */

    'number_format_validator' => NumberFormatValidator::class,

    /*
    |--------------------------------------------------------------------------
    | Verification codes
    |--------------------------------------------------------------------------
    |
    | code_generator              implements Contracts\GeneratesVerificationCodes
    | code_length                 digits in a code
    | verification_code_validity  minutes a code stays valid
    | max_attempts                wrong codes before the number is locked
    | attempt_expiry              minutes a locked number stays locked
    | resend_interval             seconds before another code can be sent
    |
    */

    'code_generator' => VerificationCodeGenerator::class,

    'code_length' => 6,

    'verification_code_validity' => 10,

    'max_attempts' => 5,

    'attempt_expiry' => 30,

    'resend_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    |
    | Requests per minute from one IP address, on top of the per-number
    | limits above: `send` for requesting codes, `verify` for entering them.
    |
    */

    'rate_limits' => [
        'send' => 5,
        'verify' => 10,
    ],

    /*
    | Extra validation rules for requesting a code, e.g. a captcha:
    | ['g-recaptcha-response' => ['required', 'captcha']]
    */

    'request_code_rules' => [],

    /*
    |--------------------------------------------------------------------------
    | The SMS
    |--------------------------------------------------------------------------
    |
    | Sent through javaabu/sms-notifications (config/sms.php picks the
    | provider). Extend the class to change the message.
    |
    */

    'notification' => VerificationCodeNotification::class,

    /*
    |--------------------------------------------------------------------------
    | Logging in
    |--------------------------------------------------------------------------
    |
    | A code sent by SMS replaces the password, so users who turned on
    | two-factor authentication still get its challenge. Set this to true to
    | let the mobile login skip it.
    |
    */

    'skip_two_factor' => false,

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | Whether user types with mobile login and javaabu/auth's `registration`
    | feature can also sign up with a mobile number, and the javaabu/stencil
    | form that asks for their details once the number is verified.
    |
    */

    'registration' => true,

    'registration_form' => MobileRegistrationForm::class,

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | Replace any controller with your own, usually extending the package's.
    |
    */

    'controllers' => [
        'login' => Controllers\MobileLoginController::class,
        'register' => Controllers\MobileRegistrationController::class,
        'mobile_number' => Controllers\MobileNumberController::class,
        'mobile_number_actions' => Controllers\MobileNumberActionsController::class,
        'api' => Controllers\MobileApiController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | The policy behind the admin actions on other users' numbers
    | (Route::mobileNumberActions()), and the permissions registered with
    | javaabu/permissions for each user type: all types when
    | managed_user_types is null.
    |
    */

    'policy' => MobileNumberPolicy::class,

    'register_permissions' => true,

    'managed_user_types' => null,

    /*
    |--------------------------------------------------------------------------
    | Passport
    |--------------------------------------------------------------------------
    |
    | With laravel/passport installed, the package adds a `mobile` grant:
    | an access token for a number and its code. Clients need the grant (or
    | the password grant), and their provider must belong to a user type.
    |
    */

    'passport_grant' => true,

];
