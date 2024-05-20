---
title: How to Login with Mobile Number
sidebar_position: 1.8
---

# How to Login with Mobile Number
You can use this package to log in users with their mobile numbers. Before diving into the steps, let's see how the login process works.

## Login Process
The login process with mobile number works as follows:

1. A user sends a request to the application with their mobile number.
2. The package checks if the mobile number is registered.
    3. If the mobile number is not registered, the package returns a message that the mobile number is not registered.
    4. If the mobile number is registered, the package sends an OTP to the mobile number.
2. The user enters the OTP.
2. The package verifies the OTP.
3. The user is then authenticated and logged in.

## Step 1: Create the Controller
Create a controller class that extends `Javaabu\Helpers\Http\Controllers\Controller` class, implements `Javaabu\MobileVerification\Contracts\LoginWithMobileNumberContract` interface and add the `Javaabu\MobileVerification\Traits\LoginsWithMobileNumber` trait. Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\LoginsWithMobileNumber;
use Javaabu\MobileVerification\Contracts\LoginWithMobileNumberContract;

class LoginController extends Controller implements LoginWithMobileNumberContract
{
    use LoginsWithMobileNumber;
    
    /*
     * Disallow authenticated users from accessing the login page
     * */
    public function __construct()
    {
        $this->middleware('guest:' . $this->getGuardName());
    }
    
    /*
     * The authenticatable user class to use
     * */
    public function getUserClass(): string
    {
        return User::class;
    }
    
    public function getGuardName(): string
    {
        return 'web';
    }
    
    public function getVerificationCodeRequestFormView(): ?string
    {
        return "verification-code-request-form";
    }
    
    /*
     * This is the view that will be returned when the 
     * user is required to enter the verification code
     * */
    public function getVerificationCodeFormView(): ?string
    {
        return "verification-code-entry-form";
    }
    
    /*
     * The URL to redirect to when the verification code is validated,
     * for example, the dashboard URL
     * */
    public function verificationCodeSuccessRedirectUrl(): string
    {
        // TODO: Implement verificationCodeSuccessRedirectUrl() method.
    }
    
    public function enableReCaptcha(): bool
    {
        return true;
    }
}
```

## Step 2: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::group([
    'middleware' => 'web',
    'prefix' => '/mobile-verification',
], function () {
    
    /*
     * This route will show the form where the user will enter their 
     * mobile number and request for the verification code
     * */
    Route::get('/login', [LoginController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.login.create');
    
    /*
     * This route will handle the request and send the verification code
     * if the mobile number is valid and registered and shows a form
     * to enter the verification code if the mobile number is valid.
     * */
    Route::post('/login', [LoginController::class, 'requestVerificationCode'])->name('mobile-verifications.login.store');
    
    /*
     * This route will handle the validation of the verification code sent from the
     * previous route and log in the user if the verification code is valid.
     * */
    Route::match(['PATCH', 'PUT'], '/login', [LoginController::class, 'verifyVerificationCode'])->name('mobile-verifications.login.update');

});
```

## Step 3: Making The Requests

### Requesting the OTP
Below is an example of how you can make the request in your application.

| Attribute    | Description                                                                         | Type   | Required |
|--------------|-------------------------------------------------------------------------------------|--------|----------|
| country_code | The country code of the mobile number. When not given, default country code is used | String | No       |
| number       | The mobile number to validate.                                                      | String | Yes      |

### Requesting To Login

| Attribute         | Description                                                                                      | Type   | Required |
|-------------------|--------------------------------------------------------------------------------------------------|--------|----------|
| country_code      | The country code of the mobile number. When not given, default country code is used              | String | No       |
| number            | The mobile number to validate.                                                                   | String | Yes      |
| verification_code | The OTP sent to the mobile number.                                                               | String | Yes      |
| verification_code_id | The ID of the verification code. This is automatically sent to the verification code entry form. | String | Yes      |






