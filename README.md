# Mobile Verification

[![Tests](https://github.com/Javaabu/mobile-verification/actions/workflows/tests.yml/badge.svg?branch=next-gen)](https://github.com/Javaabu/mobile-verification/actions/workflows/tests.yml?query=branch%3Anext-gen)
[![codecov](https://codecov.io/gh/Javaabu/mobile-verification/branch/next-gen/graph/badge.svg)](https://codecov.io/gh/Javaabu/mobile-verification/tree/next-gen)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/mobile-verification.svg)](https://packagist.org/packages/javaabu/mobile-verification)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

Log in, sign up and verify mobile numbers with one-time SMS codes in Laravel. Built on [javaabu/auth](https://github.com/Javaabu/auth): mobile login is a login method of any user type (admins, public users, …), two-factor authentication still applies, and the pages ship in Blade (javaabu/forms) and Inertia React (@javaabu/forms). Codes expire, wrong codes lock the number for a while, requests are rate limited, SMSs go through [javaabu/sms-notifications](https://github.com/Javaabu/sms-notifications), and everything is translated to Dhivehi.

## Documentation

You'll find the documentation on [https://docs.javaabu.com/mobile-verification](https://docs.javaabu.com/mobile-verification), and in the [`docs`](docs) folder.

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving this package? Feel free to create an [issue](../../issues) on GitHub, we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Quick start

```bash
composer require javaabu/mobile-verification
php artisan migrate
```

```php
// config/javaabu-auth.php: add the login method to a user type
use Javaabu\MobileVerification\Auth\MobileLoginMethod;

'user' => [
    'guard' => 'web',
    'route_name' => '',
    'home' => '/dashboard',
    'login_methods' => [MobileLoginMethod::class],
],
```

```php
// routes/web.php
Route::authRoutes('user');            // /login/mobile and /register/mobile
Route::mobileNumberRoutes('user');    // /settings/mobile-number

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

Upgrading from v2? See [`docs/upgrading.md`](docs/upgrading.md). For AI assistants, the package ships [`llms.txt`](llms.txt) and a Laravel Boost guideline and skill.

## Testing

```bash
composer test
composer lint
pnpm install && pnpm check   # the React pages
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Arushad Ahmed (@dash8x)](http://arushad.com)
- [Hussa Afeef (@ibnnajjaar)](https://abunooh.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
