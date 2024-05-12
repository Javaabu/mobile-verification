---
title: How to Login with Mobile Number
sidebar_position: 1.8
---

# How to Login with Mobile Number
You can use this package to log in users with their mobile numbers. Before diving into the steps, let's see how the login process works.

## Login Process
The login process with mobile number works as follows:
**Note:** The process assumes that the user has an OTP. To see how to request for an OTP, see [How to Request for OTP](how-to-request-otp.md).
1. The user then enters the OTP.
2. The package verifies the OTP.
3. The user is then authenticated and logged in.

## Step 1: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login'])->name('mobile-numbers.login');
```

And if you are not doing an API request, you can define the route from which the request is coming from.
```php
use Illuminate\Support\Facades\Route;

Route::get('login-form', [LoginController::class, 'showLoginForm'])->name('mobile-numbers.login.show');
```

## Step 2: Create the Controller
Create a controller that extends the `Javaabu\MobileVerification\Http\Controllers\LoginController` class. Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Javaabu\MobileVerification\Http\Controllers\LoginController as BaseLoginController;

class LoginController extends BaseLoginController
{
    /*
     * Define the user class to be used for mobile number validation
     */
    public string $user_class = User::class;
    
    /*
     * Define the guard to be used for the user
     */
    protected string $user_guard = 'web';
    
    /*
     * Define the user column to be used for mobile number validation
     */
    public string $form_view = 'web.mobile-numbers.login-form';
}
```

## Step 3: Make the Request
Make a request to the application with the mobile number and OTP. Below is an example of how you can make the request in your application.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code | The country code of the mobile number. | String | Yes |
| number | The mobile number to validate. | String | Yes |
| token | The OTP sent to the mobile number. | String | Yes |

```json
{
    "country_code": "960",
    "number": "7825222",
    "token": "123456"
}
```




