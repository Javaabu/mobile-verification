<?php

namespace Javaabu\MobileVerification\Support;

/**
 * The English strings of the React pages, sent translated as the `strings` prop.
 * They are the strings the Blade pages use, so one lang/dv.json covers both.
 */
class Strings
{
    public const array ALL = [
        'back_to_login' => 'Log in with a password instead',
        'change_number' => 'Change number',
        'code' => 'Code',
        'code_help' => 'We sent a :length digit code to :number. It expires in :minutes minutes.',
        'continue' => 'Continue',
        'country_code' => 'Country code',
        'create_account' => 'Create account',
        'current_number' => 'Your mobile number is :number.',
        'have_account' => 'Already have an account?',
        'log_in' => 'Log in',
        'log_in_with_mobile' => 'Log in with your mobile number',
        'mobile_number' => 'Mobile number',
        'mobile_number_help' => 'We will send you a code by SMS.',
        'new_mobile_number' => 'New mobile number',
        'no_account' => "Don't have an account?",
        'no_number' => 'You have not verified a mobile number yet.',
        'register_details_help' => 'Your number :number is verified. Tell us a little about yourself.',
        'register_details_title' => 'Your details',
        'register_with_mobile' => 'Sign up with your mobile number',
        'remember_me' => 'Remember me',
        'resend_code' => 'Send a new code',
        'resend_in' => 'You can ask for a new code in :seconds seconds.',
        'send_code' => 'Send code',
        'settings_mobile_title' => 'Mobile number',
        'settings_mobile_help' => 'Your mobile number is used to log in and for SMS notifications.',
        'sign_up' => 'Sign up',
        'verify' => 'Verify',
    ];

    /** @return array<string, string> */
    public static function translated(): array
    {
        $strings = [];

        foreach (self::ALL as $key => $english) {
            // Placeholders stay for the page to fill
            $strings[$key] = __($english);
        }

        return $strings;
    }
}
