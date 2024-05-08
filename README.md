# Mobile Verification

[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/mobile-verification.svg?style=flat-square)](https://packagist.org/packages/javaabu/mobile-verification)
[![Test Status](../../actions/workflows/run-tests.yml/badge.svg)](../../actions/workflows/run-tests.yml)
[![Code Style Status](../../actions/workflows/php-cs-fixer.yml/badge.svg)](../../actions/workflows/php-cs-fixer.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/javaabu/mobile-verification.svg?style=flat-square)](https://packagist.org/packages/javaabu/mobile-verification)

Adds mobile number verification to Laravel projects.

## Documentation

You'll find the documentation on [https://docs.javaabu.com/docs/mobile-verification](https://docs.javaabu.com/docs/mobile-verification).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving this package? Feel free to create an [issue](../../issues) on GitHub, we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Installation

Publishing Migrations
```bash
php artisan vendor:publish --provider="Javaabu\MobileVerification\MobileVerificationServiceProvider" --tag="mobile-verification-migrations"
```

Publishing Config
```bash
php artisan vendor:publish --provider="Javaabu\MobileVerification\MobileVerificationServiceProvider" --tag="mobile-verification-config"
```

Publishing Translations
```bash
php artisan vendor:publish --provider="Javaabu\MobileVerification\MobileVerificationServiceProvider" --tag="mobile-verification-translations"
```

### Verifying Mobile Number Availability
Create a route to verify mobile number availability
```php
Route::post('validate', [MobileNumberAvailibilityController::class, 'validate']);
```

In your controller
```php
use Javaabu\MobileVerification\Traits\ValidatesMobileNumbers;

// Specify the user type
protected string $user_class = 'user';
```

To redirect the user if the mobile number is available, you can override the `redirectUrl` method
```php
    public function redirectUrl(): RedirectResponse|JsonResponse
    {
        return to_route('web.home')->with(['message' => __('The mobile number is valid')]);
    }
```
When you call the `validate` method, it will return a message that the mobile number is already registered or that the mobile number is available.


## Getting Mobile Number Verification Code
Create a route to get the mobile number verification code
```php
Route::post('send-otp', [MobileNumberVerificationController::class, 'sendOtp']);
```

In your controller
```php
    protected string $user_class = 'user';
    
    use CanSendVerificationCode;
```

## Registering Using OTP
Create a route to register using OTP
```php
Route::post('register', [RegistrationController::class, 'register']);
```

In your controller add the `CanRegisterUsingToken` trait and implement the `IsRegistrationController` interface.
```php
    protected string $user_class = 'user';
    
    use CanRegisterUsingToken;

    /*
     * Override the below method as required
     * */
    public function registerUser(array $data): HasMobileNumber
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return $user;
    }
```




## Testing

You can run the tests with

``` bash
./vendor/bin/phpunit
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
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
