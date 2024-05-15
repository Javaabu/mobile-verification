<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile Number Database connection
    |--------------------------------------------------------------------------
    | This is the database connection that will be used by the mobile numbers
    | migration and the MobileNumber model shipped with this package. In case
    | it's not set Laravel's database.default will be used instead.
    */
    'database_connection' => env('MOBILE_NUMBER_DB_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Mobile Numbers table name
    |--------------------------------------------------------------------------
    | This is the name of the table that will be created by the mobile numbers
    | migration and used by the MobileNumber model shipped with this package.
    */
    'table_name' => 'mobile_numbers',

    /*
    |--------------------------------------------------------------------------
    | Mobile Number Model
    |--------------------------------------------------------------------------
    | This model will be used to save mobile numbers.
    | It should implement the Javaabu\MobileVerification\Contracts\MobileNumber interface
    | and extend Illuminate\Database\Eloquent\Model.
    */
    'mobile_number_model' => \Javaabu\MobileVerification\Models\MobileNumber::class,

    /*
    |--------------------------------------------------------------------------
    | Default country code
    |--------------------------------------------------------------------------
    | The country code to be used by default
    */
    'default_country_code' => env('MOBILE_NUMBER_DEFAULT_COUNTRY_CODE', '960'),

    /*
    |--------------------------------------------------------------------------
    | Allowed country codes
    |--------------------------------------------------------------------------
    | Which country codes are allowed
    */
    'allowed_country_codes' => explode(',', env('MOBILE_NUMBER_ALLOWED_COUNTRY_CODES', '960')),

    /*
    |--------------------------------------------------------------------------
    | Mobile grant allowed client providers
    |--------------------------------------------------------------------------
    | List of user providers that can be used to be authenticated using mobile number grant type
     * */
    'mobile_grant_allowed_providers' => ['users'],

    /*
    |--------------------------------------------------------------------------
    | Number prefix
    |--------------------------------------------------------------------------
    | The prefix to add before fully qualified numbers
    */
    'number_prefix' => '+',

    /*
    |--------------------------------------------------------------------------
    | Mobile Number Format Validator
    |--------------------------------------------------------------------------
    | This class that will be used to validate the mobile number format.
     * */
    'mobile_number_format_validator' => \Javaabu\MobileVerification\Support\MobileNumberFormatValidator::class,

    /*
    |--------------------------------------------------------------------------
    | Verification code validity
    |--------------------------------------------------------------------------
    | The number of minutes that a mobile number verification code
    | would be valid for.
    */
    'verification_code_validity' => 10, // minutes

    /*
    |--------------------------------------------------------------------------
    | Maximum attempts
    |--------------------------------------------------------------------------
    | The maximum number of validation attempts that can be made before which
    | the number would get locked for a specific length of time.
    */
    'max_attempts' => 5,

    /*
    |--------------------------------------------------------------------------
    | Attempt expiry
    |--------------------------------------------------------------------------
    | The number of minutes that a number would get locked for if there are too
    | many verification attempts for that number.
    */
    'attempt_expiry' => 30, // mins

    /*
    |--------------------------------------------------------------------------
    | Resend interval
    |--------------------------------------------------------------------------
    | The number of seconds after which a verification code can be resent to
    | the same mobile number.
    */
    'resend_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Verified
    |--------------------------------------------------------------------------
    | Whether the mobile number should be verified
    */
    'verified' => env('MOBILE_NUMBER_VERIFIED', true),

    /*
    |--------------------------------------------------------------------------
    | Use Recaptcha
    |--------------------------------------------------------------------------
    | Whether the use Recaptcha to prevent SPAM OTP requests
    */
    'use_recaptcha' => env('MOBILE_NUMBER_USE_RECAPTCHA', true),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
     * */
    'notifications' => [
        'login' => \Javaabu\MobileVerification\Notifications\LoginNotification::class,
        'verification_code' => \Javaabu\MobileVerification\Notifications\MobileNumberVerificationToken::class,
        'update' => \Javaabu\MobileVerification\Notifications\UpdateMobileNumberNotification::class,
    ]
];
