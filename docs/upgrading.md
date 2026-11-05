---
title: Upgrading from v2
sidebar_position: 1.25
---

v3 is a rewrite on top of [javaabu/auth](https://github.com/Javaabu/auth) v3. The table and the request field names (`number`, `country_code`, `verification_code`, `verification_code_id`) are the same; the controllers you wrote with v2's traits are replaced by the package's own.

# Requirements

- PHP 8.4+, Laravel 12+.
- javaabu/auth v3 (required now; it was a suggestion). Set up its user types first: see its upgrade guide.
- javaabu/helpers, javaabu/activitylog and javaabu/laravel-recaptcha are no longer installed. If your app used them through this package, require them yourself.
- laravel/passport is optional (it was required). Install it if you use the `mobile` grant.

# Database

Nothing to migrate. The package now loads its migration itself, and it skips the table when it exists. If you published v2's migration, keep it and call `MobileVerification::ignoreMigrations()`, or delete your copy.

# Config

Publish the new config and copy your values over. Renamed and removed keys:

| v2 | v3 |
| --- | --- |
| `mobile_number_format_validator` | `number_format_validator` (implements `Contracts\ValidatesNumberFormat`) |
| `verification_code_generator` | `code_generator` (implements `Contracts\GeneratesVerificationCodes`) |
| `notifications` (`login`, `register`, `verification_code`, `update`) | `notification`: one class, given the purpose |
| `use_recaptcha` | removed: add a captcha rule to `request_code_rules` |
| `mobile_grant_allowed_providers` | removed: the grant serves the providers of javaabu/auth user types; `passport_grant` turns it off |
| `verified` | removed |
| | new: `views`, `code_length`, `rate_limits`, `request_code_rules`, `skip_two_factor`, `registration`, `registration_form`, `controllers`, `policy`, `register_permissions`, `managed_user_types` |

# User models

```diff
- use Javaabu\MobileVerification\Traits\InteractsWithMobileNumbers;
+ use Javaabu\MobileVerification\Concerns\InteractsWithMobileNumbers;
  use Javaabu\MobileVerification\Contracts\HasMobileNumber;
```

The trait keeps `phone()`, the attributes, the scopes, `updatePhone()`, `updateMobileNumber()`, `clearPhones()` and `hasVerifiedMobile()`. Changes:

- `updateMobileNumber()` returns the `MobileNumber`, or null when another user has the number, and takes a causer.
- New: `removeMobileNumber()`, `findByMobileNumber()`, `shouldVerifyPhone()` (replaces the `should_verify_phone` attribute and `scopeShouldVerifyPhone`).
- Removed: `phoneVerificationUrlPrefix()`, `redirectToMobileVerificationUrl()` (the middleware redirects to the account page itself).
- `HasMobileNumber` lists the methods the package calls.

# The MobileNumber model

- `Contracts\MobileNumber` is gone; a custom `mobile_number_model` extends `Models\MobileNumber`.
- It no longer logs activity (javaabu/activitylog). Listen to `MobileNumberUpdated` instead; see [Events](advanced-usage/events.md).
- `$appends` is only `formatted_number` (v2 also appended `verification_code_expires_in` and `resend_verification_code_in`).
- Attributes became methods:

| v2 | v3 |
| --- | --- |
| `was_sent_recently` | `wasSentRecently()` |
| `resend_verification_code_in` | `resendVerificationCodeIn()` (seconds) |
| `is_verification_code_expired` | `isVerificationCodeExpired()` |
| `verification_code_expires_at` | `verificationCodeExpiresAt()` |
| `verification_code_expiry` | `verificationCodeExpiresIn()` (seconds) |
| `verification_code_expires_in` | `codeValidity()` (minutes) |
| `is_locked` | `isLocked()` |
| `can_request_code` | `canRequestCode()` |
| `attempts_expiry_at`, `attempts_expiry`, `attempts_expiry_seconds` | `lockExpiresAt()`, `lockExpiresIn()` (seconds) |
| `verifyVerificationCode($code, $reset)` | `checkVerificationCode($code)`, or the `VerifyVerificationCode` action |
| `randomVerificationCode()` | the code generator |
| `blankPhone($number, $country_code, $user_type)` | `MobileVerification::findOrCreate($type, $country_code, $number)` |
| `getUserByMobileNumber(...)` | `MobileVerification::find(...)` and `MobileVerification::userFor($type, $mobile_number)` |

- `verificationCodeResponseData()`, which the JSON responses return: `expires_in` is now the seconds left (it was the validity in minutes, and `expiry_duration` the seconds left); `attempt_expiry` (minutes) is now `unlocks_in` (seconds); `user_type` is gone; `number`, `country_code`, `formatted_number` and `remaining_attempts` are new. `verification_code_id`, `is_registered`, `expires_at`, `resend_interval`, `resend_in`, `attempts` and `is_locked` are unchanged.

# Controllers and routes

v2 shipped traits for controllers you wrote and routed yourself (`LoginsWithMobileNumber`, `RegistersWithMobileNumber`, `UpdatesMobileNumber`, `SendsVerificationCode`, `VerifiesVerificationCode`, `HasRequestMobileNumber`, `HasUserType`, `UsesGuard`, `UsesSessionMobileNumber` and their contracts). They're gone. Delete your controllers and routes and:

1. Add `MobileLoginMethod` to the user types ([Installation](installation-and-setup.md#turning-on-mobile-login)): `Route::authRoutes()` registers the login and registration.
2. Register the account page with `Route::mobileNumberRoutes()`.
3. Move what your controllers customised: the registration fields to a [stencil form](basic-usage/registration.md#the-form), `createUser()` to a `CreatesUsersFromMobileNumbers` action, other steps to a [controller from config](advanced-usage/overriding.md).

The URLs changed (`login/mobile`, `register/mobile`, `settings/mobile-number`); update links and mobile apps that posted to your old routes. API clients can use `Route::mobileApiRoutes()`.

v2's login logged users in directly. v3 hands over to javaabu/auth, so **two-factor authentication now applies** to mobile logins; set `skip_two_factor` to keep v2's behaviour.

# Middleware

`mobile-verified` still works; prefer `mobile.verified:{type}`. It redirects to the type's `mobile-number` page instead of calling `redirectToMobileVerificationUrl()` on the user.

# Rules

- `IsValidMobileNumber`'s first argument is a javaabu/auth user type (name or `UserType`); pass a morph class with `is_morph_class: true`. `canSendOtp()` is gone: `SendVerificationCode` checks locks and the resend interval.
- `IsValidVerificationCode` is gone: a wrong code counts as an attempt, which a validation rule shouldn't do. Use the `VerifyVerificationCode` action.

# Notifications

`LoginVerificationTokenNotification`, `RegisterVerificationTokenNotification` and `MobileNumberVerificationToken` are replaced by `VerificationCodeNotification($code, $purpose, $name)`. The message is translated (English and Dhivehi) and uses `config('app.name')` instead of `get_setting('app_name')`.

# Passport

The `mobile` grant takes the same parameters. The user type comes from the client's provider (the javaabu/auth type whose guard uses it), and users with two-factor authentication must send `code` or `recovery_code`.

# Translations

`lang/{locale}/strings.php` (keys like `mobile-verification::strings.validation.number.invalid`) became JSON translations with English keys (`lang/dv.json`). Republish and redo overrides with the new keys.

# Views

v2 had no views. v3 ships Blade and Inertia React pages; see [Pages](basic-usage/pages.md).
