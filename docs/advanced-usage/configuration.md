---
title: Configuration
sidebar_position: 1
---

Publish the config with `php artisan vendor:publish --tag=mobile-verification-config`. Every option has a default, so the file only needs what you change.

| Option | Default | What |
| --- | --- | --- |
| `views` | `null` (env `MOBILE_VERIFICATION_VIEWS`) | `blade` or `inertia`; null follows the javaabu/auth user type |
| `database_connection` | `null` (env `MOBILE_NUMBER_DB_CONNECTION`) | Connection of the mobile numbers table; null is the default connection |
| `table_name` | `mobile_numbers` | The table |
| `mobile_number_model` | `Models\MobileNumber` | The model; must extend the package's |
| `default_country_code` | `960` (env `MOBILE_NUMBER_DEFAULT_COUNTRY_CODE`) | Used when no country code is given |
| `allowed_country_codes` | `['960']` (env `MOBILE_NUMBER_ALLOWED_COUNTRY_CODES`, comma separated) | Country codes users can choose |
| `number_prefix` | `+` | Shown before the country code |
| `number_format_validator` | `Support\NumberFormatValidator` | Implements `Contracts\ValidatesNumberFormat`; binding the contract wins |
| `code_generator` | `Support\VerificationCodeGenerator` | Implements `Contracts\GeneratesVerificationCodes`; binding the contract wins |
| `code_length` | `6` | Digits in a code (at least 4) |
| `verification_code_validity` | `10` | Minutes a code stays valid, and a verified registration number stays usable |
| `max_attempts` | `5` | Wrong codes before the number is locked |
| `attempt_expiry` | `30` | Minutes a locked number stays locked, from when its code was sent |
| `resend_interval` | `30` | Seconds before another code can be sent to a number |
| `rate_limits.send` | `5` | Code requests per minute per IP and user type |
| `rate_limits.verify` | `10` | Code entries per minute per IP, session and user type |
| `request_code_rules` | `[]` | Extra validation rules for requesting a code, e.g. a captcha |
| `notification` | `Notifications\VerificationCodeNotification` | The SMS |
| `skip_two_factor` | `false` | Let the mobile login skip javaabu/auth's two-factor challenge |
| `registration` | `true` | Mobile registration for types with javaabu/auth's `registration` feature |
| `registration_form` | `Stencil\MobileRegistrationForm` | The javaabu/stencil details form |
| `controllers` | the package's | `login`, `register`, `mobile_number`, `mobile_number_actions`, `api` |
| `policy` | `Policies\MobileNumberPolicy` | The admin actions' policy |
| `register_permissions` | `true` | Register the admin permissions with javaabu/permissions |
| `managed_user_types` | `null` | Types to register permissions for; null is every type |
| `passport_grant` | `true` | The `mobile` grant, when laravel/passport is installed |

Where it fits, the package uses javaabu/auth's settings instead of its own: the user types, guards, homes, layouts (`javaabu-auth.layouts`, `layout_section`), the view stack, two-factor modes, `javaabu-auth.api` and `api_tokens`.

# A captcha

`request_code_rules` are added to the validation of every code request (login, registration, account page and API), so a captcha package plugs in without overriding controllers:

```php
'request_code_rules' => [
    'g-recaptcha-response' => ['required', 'captcha'],
],
```

Add the captcha field to the number forms by publishing the views.
