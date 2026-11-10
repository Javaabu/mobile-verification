---
title: Overriding controllers, actions and views
sidebar_position: 4
---

# Controllers

Every route's controller comes from config. Extend the package's and set yours:

```php
// config/mobile-verification.php
'controllers' => [
    'login' => App\Http\Controllers\MobileLoginController::class,
    'register' => Javaabu\MobileVerification\Http\Controllers\MobileRegistrationController::class,
    'mobile_number' => Javaabu\MobileVerification\Http\Controllers\MobileNumberController::class,
    'mobile_number_actions' => Javaabu\MobileVerification\Http\Controllers\MobileNumberActionsController::class,
    'api' => Javaabu\MobileVerification\Http\Controllers\MobileApiController::class,
],
```

The login, registration and account controllers extend `VerificationFlowController`, which has the shared steps (`store` sends a code, `verify` shows the code page, `confirm` checks it, `resend`) and calls `verified()` once the code is right:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Javaabu\MobileVerification\Http\Controllers\MobileLoginController as BaseController;
use Javaabu\MobileVerification\Models\MobileNumber;
use Symfony\Component\HttpFoundation\Response;

class MobileLoginController extends BaseController
{
    protected function verified(Request $request, MobileNumber $mobile_number): Response
    {
        activity()->performedOn($mobile_number)->log('Logged in with a mobile number');

        return parent::verified($request, $mobile_number);
    }
}
```

Controllers are read when the routes are registered, so set them before `Route::authRoutes()` runs (config is).

# Actions and contracts

| Contract or class | Default | For |
| --- | --- | --- |
| `Contracts\CreatesUsersFromMobileNumbers` | `Actions\CreateUserFromMobileNumber` | Creating users at the end of registration |
| `Contracts\GeneratesVerificationCodes` | `Support\VerificationCodeGenerator` | The codes |
| `Contracts\ValidatesNumberFormat` | `Support\NumberFormatValidator` | Number formats per country code |
| `Actions\SendVerificationCode` | | Checks, generates and sends a code |
| `Actions\VerifyVerificationCode` | | Checks a code, counts attempts, locks |
| `Actions\AuthenticateMobileNumber` | | The API and Passport grant: number, code, account status, two-factor |

Bind your own in a service provider's `register()`:

```php
$this->app->bind(\Javaabu\MobileVerification\Contracts\ValidatesNumberFormat::class, \App\Support\NumberFormat::class);
```

javaabu/auth's responses (`LoginResponse`, `RegisterResponse`, `TwoFactorChallengeResponse`) decide where users go after the mobile login and registration too; rebind them there.

# The registration form

Set `registration_form` to your own javaabu/stencil form class; see [Registration](../basic-usage/registration.md#the-form).

# Blade views

```bash
php artisan vendor:publish --tag=mobile-verification-views
```

Edit `resources/views/vendor/mobile-verification/login.blade.php` and the others. The layouts are javaabu/auth's, per user type.

# React pages

```bash
php artisan manifest:override mobile-verification Pages/Login.tsx
```

Or return your own page from `resolvePage` for `mobile-verification::Login`. Keep the props in [Pages](../basic-usage/pages.md).

# Different stacks

The stack follows the user type, so admins can use Blade while public users get React. `mobile-verification.views` overrides it for every type.
