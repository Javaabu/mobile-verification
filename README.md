# Mobile Verification

[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/mobile-verification.svg?style=flat-square)](https://packagist.org/packages/javaabu/mobile-verification)
[![Test Status](../../actions/workflows/run-tests.yml/badge.svg)](../../actions/workflows/run-tests.yml)
![Code Coverage Badge](./.github/coverage.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/javaabu/mobile-verification.svg?style=flat-square)](https://packagist.org/packages/javaabu/mobile-verification)

Adds mobile number verification to Laravel projects.

## Documentation

You'll find the documentation on [https://docs.javaabu.com/docs/mobile-verification](https://docs.javaabu.com/docs/mobile-verification).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving this package? Feel free to create an [issue](../../issues) on GitHub, we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Installation

You can install the package via composer:

```bash
composer require javaabu/mobile-verification
```

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
- [Hussa Afeef (@ibnnajjaar)](https://abunooh.com)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
