---
title: How to Login with Mobile Number
sidebar_position: 1.9
---

# How to Update Mobile Number
You can use this package to update a user's mobile number. Before diving into the steps, let's see how the update process works.

## Update Process
The update process with mobile number works as follows:
1. An authenticated user sends a request to the application with their new mobile number.
2. The package checks if the mobile number is already registered.
    3. If the mobile number is already registered, the package returns a message that the mobile number is already registered.
    4. If the mobile number is not registered, the package sends an OTP to the new mobile number.
5. The user then enters the OTP.
6. The package verifies the OTP.
7. The package then assigns the verified mobile number to the user and deletes the old mobile number record from the database.

## Step 1: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;

Route::post('update-mobile-number-otp', [App\Http\Controllers\UpdateMobileNumberController::class, 'requestOtp'])->name('mobile-numbers.update-mobile-number-otp');
Route::post('update-mobile-number', [App\Http\Controllers\UpdateMobileNumberController::class, 'update'])->name('mobile-numbers.update-mobile-number');
```

And if you are not doing an API request, you can define the route from which the request is coming from.

```php
use Illuminate\Support\Facades\Route;

Route::get('update-mobile-number-otp-form', [App\Http\Controllers\UpdateMobileNumberController::class, 'showUpdateOtpRequestForm'])->name('mobile-numbers.update-mobile-number-otp.show');
Route::get('update-mobile-number-form', [App\Http\Controllers\UpdateMobileNumberController::class, 'showUpdateForm'])->name('mobile-numbers.update-mobile-number.show');
```

## Step 2: Create the Controller
Create a controller that extends the `Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController` class. Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController as BaseUpdateMobileNumberController;

class UpdateMobileNumberController extends BaseUpdateMobileNumberController
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
    public string $form_view = 'web.mobile-numbers.update-mobile-number-form';
}
```
