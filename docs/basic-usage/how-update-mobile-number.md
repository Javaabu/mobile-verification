---
title: How to Update with Mobile Number
sidebar_position: 1.7
---

# How to Update Mobile Number
You can use this package to update a user's mobile number. Before diving into the implementation steps, let's see how the update process works.

## Update Process
The update process with mobile number works as follows:

1. An authenticated user sends a request to the application with their new mobile number.
2. The package checks if the mobile number is already registered.
    3. If the mobile number is already registered, the package returns a message that the mobile number is already registered.
    4. If the mobile number is not registered, the package sends an OTP to the new mobile number. 
5. The user then enters the OTP. 
6. The package verifies the OTP. 
7. The package then assigns the verified mobile number to the user and disassociates the previous mobile number from the user.


## Step 1: Create the Controller
Create a controller that extends the `Javaabu\Helpers\Http\Controllers\Controller` class, implements `Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract` interface and add `Javaabu\MobileVerification\Traits\UpdatesMobileNumber` trait to it. Below is an example of how you can create the controller in your application.

```php
<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Traits\UpdatesMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Contracts\UpdateMobileNumberContract;

class MobileNumberUpdateController extends Controller implements UpdateMobileNumberContract
{
    use UpdatesMobileNumber;

    /*
     * The authenticatable user class to use
     * */
    public function getUserClass(): string
    {
        return User::class;
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
     * and the mobile number is updated. For example, profile page.
     * */
    public function verificationCodeSuccessRedirectUrl(): string
    {
        // TODO: Implement verificationCodeSuccessRedirectUrl() method.
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
     * new mobile number and request for the verification code
     * */
    Route::get('/update', [MobileNumberUpdateController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.update.create');
    
    /*
     * This route will handle the request and send the verification code
     * if the mobile number is valid and not registered and shows a 
     * form to enter the verification code. This view is defined
     * in the `getVerificationCodeFormView` method in the controller.
     * */
    Route::post('/update', [MobileNumberUpdateController::class, 'requestVerificationCode'])->name('mobile-verifications.update.store');
    
    /*
     * This route will handle the validation of the verification code sent from the
     * previous route and updates the user mobile number if the verification code 
     * is valid. The user is then redirected to the URL defined in the
     * `verificationCodeSuccessRedirectUrl` method in the controller.
     * */
    Route::match(['PATCH', 'PUT'],'/update', [MobileNumberUpdateController::class, 'verifyVerificationCode'])->name('mobile-verifications.update.update');

});
```

## Step 3: Making The Requests
To update the mobile number, you need to make the following requests:

### Requesting The Verification Code
Below is an example of how you can make the request in your application.

| Attribute    | Description                                                                         | Type   | Required |
|--------------|-------------------------------------------------------------------------------------|--------|----------|
| country_code | The country code of the mobile number. When not given, default country code is used | String | No       |
| number       | The mobile number to validate.                                                      | String | Yes      |


### Verifying The Verification Code & Updating The Mobile Number
Below are the default required and optional fields for the registration form. You may add more fields to the registration form, however the validation rules for any additional fields must be defined in the `getRegisterFieldsValidationRules` method in the controller.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code      | The country code of the mobile number. When not given, default country code is used              | String | No       |
| number            | The mobile number to validate.                                                                   | String | Yes      |
| verification_code | The OTP sent to the mobile number.                                                               | String | Yes      |
| verification_code_id | The ID of the verification code. This is automatically sent to the verification code entry form. | String | Yes      |


