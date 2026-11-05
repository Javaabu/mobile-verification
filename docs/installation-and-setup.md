---
title: Installation & Setup
sidebar_position: 1.2
---

Set up [javaabu/auth](https://github.com/Javaabu/auth) v3 first: this package adds mobile login to its user types.

# Install

```bash
composer require javaabu/mobile-verification
php artisan migrate
```

The package loads its migration, which creates the `mobile_numbers` table (it skips it when the table is already there, as after v2). To change it, publish it and stop the package from loading its own:

```bash
php artisan vendor:publish --tag=mobile-verification-migrations
```

```php
// AppServiceProvider::register()
\Javaabu\MobileVerification\MobileVerification::ignoreMigrations();
```

# The SMS provider

Codes go out through [javaabu/sms-notifications](https://github.com/Javaabu/sms-notifications). Pick a provider in `.env` and install its channel:

```bash
composer require dash8x/dhiraagu-sms-notification   # SMS_DRIVER=dhiraagu
# or
composer require laravel-notification-channels/twilio   # SMS_DRIVER=twilio
```

In tests, `Notification::fake()` catches the SMS; see [The SMS](advanced-usage/sms.md).

# The user model

Each user model that has a mobile number implements `HasMobileNumber` with the `InteractsWithMobileNumbers` trait:

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

Numbers live in the `mobile_numbers` table, related to users by a morph, so admins and public users can each have the same number. See [The user model](basic-usage/user-models.md).

Users who sign up with a mobile number have no password, and the default registration form doesn't require an email. Make both nullable on that table:

```php
use Javaabu\Auth\Schema\UserSchema;

Schema::create('users', function (Blueprint $table) {
    UserSchema::columns($table, password_required: false, email_required: false);
});
```

# Turning on mobile login

Add the login method to each user type that logs in with a mobile number:

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
    'admin' => [
        'guard' => 'web_admin',
        'home' => '/admin',
        'features' => ['registration' => false],
        'login_methods' => [MobileLoginMethod::class],
    ],
],
```

Or in a service provider, before the routes load:

```php
use Javaabu\MobileVerification\MobileVerification;

MobileVerification::enableFor('user', 'admin');
```

`Route::authRoutes()` then registers the mobile login (and, for types with javaabu/auth's `registration` feature, the mobile registration) with the type's other guest routes, and the login page links to it:

```php
// routes/web.php
Route::authRoutes('user');
Route::authRoutes('admin', ['prefix' => 'admin']);
```

Add the account page where signed-in users verify or change their number, and require a verified number where you need one:

```php
Route::mobileNumberRoutes('user');
Route::mobileNumberRoutes('admin', ['prefix' => 'admin/settings']);

Route::middleware(['auth.type:user', 'mobile.verified:user'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});
```

That's all the common case needs: Maldivian numbers (+960), 6 digit codes valid for 10 minutes, a new code every 30 seconds, and a 30 minute lock after 5 wrong codes. Every default is in [Configuration](advanced-usage/configuration.md).

# Pages

The pages use the same stack and layouts as javaabu/auth's: Blade by default, Inertia React when the user type's `views` (or `javaabu-auth.views`) is `inertia`. Set `mobile-verification.views` to choose for this package alone. See [Pages](basic-usage/pages.md) for the React setup.

# Publishing

```bash
php artisan vendor:publish --tag=mobile-verification-config
php artisan vendor:publish --tag=mobile-verification-views
php artisan vendor:publish --tag=mobile-verification-translations
php artisan vendor:publish --tag=mobile-verification-migrations
```

# Permissions

Admin actions on other users' numbers use javaabu/permissions. After installing, sync the permissions:

```bash
php artisan permissions:sync
```

See [Managing numbers](basic-usage/managing-numbers.md).
