---
title: API reference
sidebar_position: 7
---

Namespaces are under `Javaabu\MobileVerification`.

# MobileVerification

| Method | Returns |
| --- | --- |
| `enableFor(string ...$types)` | Adds `MobileLoginMethod` to javaabu/auth user types; call before `Route::authRoutes()` |
| `enabledFor(UserType\|string $type)` | Whether the type has mobile login |
| `allowsRegistration(UserType $type)` | `registration` config and the type's `registration` feature |
| `find(UserType\|Model\|string $type, ?string $country_code, ?string $number)` | `?MobileNumber` of that kind of user |
| `findOrCreate(UserType\|Model\|string $type, ?string $country_code, string $number)` | The record, created without an owner |
| `userFor(UserType $type, ?MobileNumber $mobile_number)` | The owner, loaded through the type's guard provider |
| `morphClassFor(UserType\|Model\|string $type)` | The `user_type` column value |
| `normalizeNumber(?string)`, `normalizeCountryCode(?string)` | `'077 712-34'` → `'7771234'`, `'+960'` → `'960'` |
| `defaultCountryCode()`, `allowedCountryCodes()`, `numberPrefix()`, `countryCodeOptions()` | From config; options are `['960' => '+960']` |
| `mobileNumberModel()`, `newMobileNumber()` | The model class, a new instance |
| `skipsTwoFactor()` | `skip_two_factor` |
| `config(string $key = '', mixed $default = null)` | `config('mobile-verification.…')` |
| `ignoreMigrations()` | Don't load the package's migration |

# Routes

| Macro | Registers |
| --- | --- |
| `Route::authRoutes()` (javaabu/auth) | The mobile login and registration of types with `MobileLoginMethod` |
| `Route::mobileNumberRoutes(?string $type, array $options)` | The account page; options `prefix`, `middleware`, `domain`, `confirm_password` |
| `Route::mobileApiRoutes(?string $type, array $options)` | `mobile/code` and `mobile/token`; options `prefix`, `middleware` |
| `Route::mobileNumberActions(string $type, array $options)` | Admin actions; options `prefix`, `name`, `middleware` |

Middleware: `mobile.verified:{type}` (also `mobile-verified`).

Account navigation: the service provider registers `UserTypes::accountLink('mobile-number', 'Mobile number', position: 30)` (javaabu/auth); it shows for types with the `mobile-number` route.

# Auth\MobileLoginMethod

javaabu/auth `LoginMethod` with key `mobile` (`MobileLoginMethod::KEY`), label "Log in with your mobile number", `routes()` and `url()` (the type's `login.mobile`).

# Models\MobileNumber

| Member | |
| --- | --- |
| `user()` | `MorphTo` the owner |
| `formatted_number`, `short_formatted_number`, `prefix` | Attributes |
| `hasOwner()` | |
| `generateVerificationCode()` | A new code, saved hashed; returns the plain code |
| `hasVerificationCode()`, `checkVerificationCode(?string)` | A wrong code counts as an attempt |
| `clearVerificationCode()`, `resetAttempts()` | Forget the code (without and with saving) |
| `verificationCodeExpiresAt()`, `verificationCodeExpiresIn()`, `isVerificationCodeExpired()`, `codeValidity()` | Expiry (seconds, minutes) |
| `wasSentRecently()`, `resendVerificationCodeIn()`, `canRequestCode()` | Resend interval |
| `isLocked()`, `hasTooManyAttempts()`, `lockExpiresAt()`, `lockExpiresIn()`, `remainingAttempts()` | Locks |
| `isSame(MobileNumber)` | Same country code and number |
| `verificationCodeResponseData()` | The JSON the endpoints return |
| `routeNotificationForSms()` | The formatted number |
| Scopes `search()`, `hasNumber()`, `hasPhoneNumber()`, `hasPhoneNumberWithOwner()`, `hasPhoneNumberWithoutOwner()` | |

# Concerns\InteractsWithMobileNumbers (Contracts\HasMobileNumber)

`phone()`, `hasVerifiedMobile()`, `shouldVerifyPhone()`, `updatePhone()`, `updateMobileNumber()`, `removeMobileNumber()`, `clearPhones()`, `findByMobileNumber()`, `routeNotificationForSms()`; attributes `formatted_mobile_number`, `mobile_number`, `mobile_number_country_code`, `short_mobile_number`, `phone_verified`, `needs_phone_verification`; scopes `phoneVerified()`, `phoneUnverified()`, `hasMobileNumber()`, `hasShortMobileNumber()`, `hasPhone()`. See [The user model](../basic-usage/user-models.md).

# Actions

| Class | `__invoke` |
| --- | --- |
| `Actions\SendVerificationCode` | `(UserType $type, Purposes $purpose, ?string $country_code, string $number, ?Model $user = null, string $error_key = 'number'): MobileNumber` |
| `Actions\VerifyVerificationCode` | `(MobileNumber $mobile_number, ?string $verification_code, ?string $verification_code_id = null, string $error_key = 'verification_code'): void` |
| `Actions\AuthenticateMobileNumber` | `(UserType $type, array $input): Model` (`number`, `country_code`, `verification_code`, `verification_code_id`, `code`, `recovery_code`) |
| `Actions\CreateUserFromMobileNumber` | `create(UserType $type, array $input, MobileNumber $mobile_number): Model` |

All throw `ValidationException`.

# Other classes

| Class | |
| --- | --- |
| `Enums\Purposes` | `LOGIN`, `REGISTER`, `UPDATE` |
| `Rules\IsValidMobileNumber`, `Rules\IsValidCountryCode` | [Validation rules](../basic-usage/validation-rules.md) |
| `Notifications\VerificationCodeNotification` | `(string $verification_code, Purposes $purpose, ?string $name)` |
| `Policies\MobileNumberPolicy` | `ABILITIES`, `DESCRIPTIONS`, `update()`, `unlock()`, `remove()`, `group(UserType)`, `permission(string $action, UserType)` |
| `Passport\MobileGrant` | The `mobile` grant |
| `Stencil\MobileRegistrationForm` | The registration form |
| `Support\PendingVerification` | The session state of a flow, per type and purpose |
| `Support\Strings` | The React strings |
| `Views\ViewRenderer` | `render(UserType $type, string $page, array $data, string $layout)`, `viewStack()`, `inertiaPage()`, `sharedData()` |
| `Http\Middleware\EnsureMobileIsVerified` | `mobile.verified` |
