---
title: How to check mobile number availability
---

## Enable Features

Goto the `config/mobile-verification.php` file.

```php
    'features' => [
        Features::checkMobileAvailability([
            // You can set multiple routes and user types to check the mobile number availability
            FeatureConfig::make()
                ->setRoute('web.validate')
                ->setRedirect('web.home')
                ->setUserClass(User::class)
        ]),
        Features::sendOTPs([
            // You can set multiple routes and user types to send OTP
            FeatureConfig::make()
                ->setRoute('web.send-otp')
                ->setRedirect('web.home')
                ->setUserClass(User::class)
        ])
        Features::registerWithOTPs([
            // You can set multiple routes and user types to register using OTP
            FeatureConfig::make()
                ->setRoute('web.register')
                ->setRedirect('web.home')
                ->setUserClass(User::class)
                ->setValidationClass(RegisterUserValidationRules::class)
                ->setServiceClass(RegisterUserService::class)
        ]),
        Features::loginWithOTPs([
            // You can set multiple routes and user types to login using OTP
            FeatureConfig::make()
                ->setRoute('web.login')
                ->setRedirect('web.dashboard')
                ->setUserClass(User::class)
        ]),
        Features::sendUpdateMobileOTPs([
            // You can set multiple routes and user types to send update mobile number OTP
            FeatureConfig::make()
                ->setRoute('web.request-otp')
                ->setRedirect('web.home')
                ->setUserClass(User::class)
                ->setMiddlewares(['auth:web'])
        ]),
        Features::updateMobileNumber([
            // You can set multiple routes and user types to update mobile number
            FeatureConfig::make()
                ->setRoute('web.update-mobile-number')
                ->setRedirect('web.home')
                ->setUserClass(User::class)
                ->setMiddlewares(['auth:web'])
        ]),
    ],
```

You may choose to disable all the features and use your own routes and controllers. In that case you may use the following traits to help you with the implementation.

//todo

```php
// todo
```
