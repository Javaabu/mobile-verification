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

## Testing

```bash
composer test
composer lint
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
