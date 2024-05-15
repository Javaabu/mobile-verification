---
title: How to Update with Mobile Number
sidebar_position: 1.9
---

# How to Update Mobile Number
You can use this package to update a user's mobile number. Before diving into the steps, let's see how the update process works.

## Update Process
The update process with mobile number works as follows:

**Note:** The process assumes that the OTP has been sent to the user's mobile number. To see how to send an OTP, see [How to Request for OTP](how-to-request-otp.md). The process also assumes that the user is authenticated.
1. The user then enters the OTP.
2. The package verifies the OTP.
3. The package then assigns the verified mobile number to the user and disassociates the previous mobile number from the user.


## Step `2`: Create the Controller
Create a controller that extends the `Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController` class. Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Javaabu\MobileVerification\Contracts\SendVerificationCodeContract;use Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract;use Javaabu\MobileVerification\Http\Controllers\UpdateMobileNumberController as BaseUpdateMobileNumberController;use Javaabu\SmsNotifications\Notifications\SendsSms;

class UpdateMobileNumberController implements 
    SendVerificationCodeContract, 
    UpdateMobileNumberContract
{
    use SendsVerificationCode;
    use UpdatesMobileNumber;
    
    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return false;
    }
    
}
```



## Step 2: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;

Route::post('update-mobile-number', [App\Http\Controllers\UpdateMobileNumberController::class, 'update'])->name('mobile-numbers.update-mobile-number');
```

And if you are not doing an API request, you can define the route from which the request is coming from.

```php
use Illuminate\Support\Facades\Route;

Route::get('update-mobile-number-form', [App\Http\Controllers\UpdateMobileNumberController::class, 'showUpdateForm'])->name('mobile-numbers.update-mobile-number-form');
```