---
title: Preparing Your Model
sidebar_position: 1.4
---

## Preparing Your Model

To associate a mobile number with a user, the user model must implement the following interface and trait:
```php
<?php

namespace App\Models;

use Javaabu\Auth\User as Authenticatable;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Traits\InteractsWithMobileNumbers;

class User extends Authenticatable implements HasMobileNumber
{
    use InteractsWithMobileNumbers;
}
```

If you want your users to have verified mobile number to access certain routes, you can implement `Javaabu\MobileVerification\Contracts\ShouldHaveVerifiedMobileNumber` interface in your user model. This interface has a method `redirectToMobileVerificationUrl()` that you can implement to redirect users to a URL if they are not verified.

To protect the routes, you can use the middleware 'mobile-verified' in your routes file. This middleware checks if the user has a verified mobile number and redirects them to the URL returned by the `redirectToMobileVerificationUrl()` method if they are not verified. You may also pass a list of guards to the middleware to check for verified mobile numbers for specific guards.

Below is an example of how you can use the middleware in your routes file:

```php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'mobile-verified:web',
], function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

```
