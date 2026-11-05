---
name: javaabu-mobile-verification
description: Mobile number login, registration and verification with one-time SMS codes using javaabu/mobile-verification on top of javaabu/auth user types. Use whenever the project depends on javaabu/mobile-verification and the task turns on mobile login or registration for a user type, reads or changes users' mobile numbers, sends or checks verification codes, protects routes with mobile.verified, customises the SMS, the registration form, the pages or the controllers, issues API tokens for a number and code (Sanctum, Passport mobile grant), or adds admin actions on users' numbers.
license: MIT
metadata:
  author: Javaabu
---

# Mobile numbers with javaabu/mobile-verification

The package owns the mobile login, registration and verification flows, their pages and the code handling; the app turns them on per javaabu/auth **user type** and protects its own routes. The docs ship with the package: `vendor/javaabu/mobile-verification/llms.txt` lists every page in `vendor/javaabu/mobile-verification/docs/`.

## Turning on mobile login for a user type

1. The user model:

```php
namespace App\Models;

use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\MobileVerification\Concerns\InteractsWithMobileNumbers;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;

class User extends BaseUser implements HasMobileNumber
{
    use InteractsWithMobileNumbers;
}
```

2. With mobile registration, users have no password: `UserSchema::columns($table, password_required: false, email_required: false)`.

3. The login method on the type, then the routes:

```php
// config/javaabu-auth.php
'user' => [
    'guard' => 'web',
    'route_name' => '',
    'home' => '/dashboard',
    'login_methods' => [\Javaabu\MobileVerification\Auth\MobileLoginMethod::class],
],
```

```php
// routes/web.php
Route::authRoutes('user');            // login/mobile, register/mobile (if the type has registration)
Route::mobileNumberRoutes('user');    // settings/mobile-number, route name `mobile-number`

Route::middleware(['auth.type:user', 'mobile.verified:user'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});
```

4. An SMS channel: `composer require dash8x/dhiraagu-sms-notification` and `SMS_DRIVER=dhiraagu` (javaabu/sms-notifications).

5. `php artisan migrate` (the package loads its `mobile_numbers` migration) and `php artisan permissions:sync`.

## Reading and changing numbers

```php
$user->phone;                         // ?MobileNumber
$user->hasVerifiedMobile();
$user->formatted_mobile_number;       // '+9607771234'
User::findByMobileNumber('7771234');

$user->updateMobileNumber('9991234', causer: $admin);   // no code; null if another user has it
$user->removeMobileNumber(causer: $admin);
```

Users change their own number on the `mobile-number` page, which sends a code to the new number first. The package already adds it to javaabu/auth's account navigation for every type with the route; don't add a link by hand (change it with `UserTypes::get('user')->accountLink(...)` or `withoutAccountLink('mobile-number')`). Custom React account pages pass `account_links` to `AccountLayout`'s `links`. Admins use `Route::mobileNumberActions('user', ['prefix' => 'admin/users', 'middleware' => ['web', 'auth.type:admin']])`.

## Codes in your own flows

```php
use Javaabu\MobileVerification\Actions\SendVerificationCode;
use Javaabu\MobileVerification\Actions\VerifyVerificationCode;
use Javaabu\MobileVerification\Enums\Purposes;

$mobile_number = app(SendVerificationCode::class)($type, Purposes::UPDATE, $country_code, $number, $user);

// throws a ValidationException on `verification_code`; the right code is used up
app(VerifyVerificationCode::class)($mobile_number, $request->input('verification_code'), $mobile_number->verification_code_id);
```

## Customising

| Change | How |
| --- | --- |
| Registration fields | Extend `Stencil\MobileRegistrationForm`, set `mobile-verification.registration_form` |
| How users are created | Bind `Contracts\CreatesUsersFromMobileNumbers` |
| The SMS | Extend `Notifications\VerificationCodeNotification`, set `notification`; or change the translation |
| Number formats | Bind `Contracts\ValidatesNumberFormat` |
| A step of a flow | Extend the controller, set it in `mobile-verification.controllers` |
| A captcha | `request_code_rules` |
| Two-factor after a mobile login | On by default; `skip_two_factor` turns it off |
| Pages | `vendor:publish --tag=mobile-verification-views`, `php artisan manifest:override mobile-verification Pages/Login.tsx` |

## References

- [Common mistakes](references/common-mistakes.md)
- [Testing](references/testing.md)
