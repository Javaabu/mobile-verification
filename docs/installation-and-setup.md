---
title: Installation & Setup
sidebar_position: 1.2
---

## Installation

You can install the package via composer:

```bash
composer require javaabu/mobile-verification
```

Publishing Config
```bash
php artisan vendor:publish --provider="Javaabu\MobileVerification\MobileVerificationServiceProvider" --tag="mobile-verification-config"
```

Publishing Migrations
```bash
php artisan vendor:publish --provider="Javaabu\MobileVerification\MobileVerificationServiceProvider" --tag="mobile-verification-migrations"
```

Publishing Translations
```bash
php artisan vendor:publish --provider="Javaabu\MobileVerification\MobileVerificationServiceProvider" --tag="mobile-verification-translations"
```
