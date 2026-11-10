---
title: Introduction
sidebar_position: 1.0
---

# Mobile Verification

[javaabu/mobile-verification](https://github.com/Javaabu/mobile-verification) lets users log in, sign up and verify their mobile number with a one-time code sent by SMS. It is built on [javaabu/auth](https://github.com/Javaabu/auth), so it works for every user type (public users, admins, staff, …) with that type's guard, routes, layouts and rules:

- **Mobile login.** A javaabu/auth login method: the login page links to it, and once the code is right, javaabu/auth finishes the login. Two-factor authentication still applies.
- **Mobile registration.** Verify a number, then fill in a short form (a [javaabu/stencil](https://github.com/Javaabu/stencil) form you can replace) to create the account.
- **Verifying and changing numbers.** An account page for signed-in users, and a `mobile.verified` middleware for pages that need a verified number.
- **Safe codes.** Hashed, expiring codes, a resend interval, a lock after too many wrong codes, and rate limits per IP.
- **SMS** through [javaabu/sms-notifications](https://github.com/Javaabu/sms-notifications) (Dhiraagu, Twilio), in the user's language.
- **API.** Sanctum or Passport tokens for a number and code, and an optional Passport `mobile` grant.
- **Blade and Inertia React.** Every page in javaabu/forms Blade components and in React with @javaabu/forms, following the user type's choice. Dhivehi and right to left throughout.
- **Overridable.** Controllers from config, actions from the container, routes from macros, views publishable, and admin actions behind a policy and javaabu/permissions permissions.

```php
// config/javaabu-auth.php
use Javaabu\MobileVerification\Auth\MobileLoginMethod;

'user_types' => [
    'user' => [
        'guard' => 'web',
        'route_name' => '',
        'home' => '/dashboard',
        'login_methods' => [MobileLoginMethod::class],
    ],
],
```

```php
// routes/web.php
Route::authRoutes('user');            // adds /login/mobile and /register/mobile
Route::mobileNumberRoutes('user');    // adds /settings/mobile-number

Route::middleware(['auth.type:user', 'mobile.verified:user'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});
```

```php
// app/Models/User.php
use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\MobileVerification\Concerns\InteractsWithMobileNumbers;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;

class User extends BaseUser implements HasMobileNumber
{
    use InteractsWithMobileNumbers;
}
```

Start with [Installation & Setup](installation-and-setup.md). Coming from v2? Read [Upgrading](upgrading.md).
