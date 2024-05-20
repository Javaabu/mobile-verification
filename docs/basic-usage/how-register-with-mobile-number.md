---
title: How to Register with Mobile Number
sidebar_position: 1.7
---

# How to Register with Mobile Number
You can use this package to register users with their mobile numbers. Before diving into the implementation steps, let's see how the registration process works.

## Registration Process
The registration process with mobile number works as follows:

1. A guest user sends a request to the application with their mobile number.
2. The package checks if the mobile number is already registered.
    3. If the mobile number is already registered, the package returns a message that the mobile number is already registered.
    4. If the mobile number is not registered, the package sends an OTP to the mobile number. 
5. The user then enters the OTP and any other details required for registration. 
6. The package verifies the OTP. 
7. The developer can then implement the logic to verify the other details and user registration. 
8. The package will then assign the verified mobile number to the user registered in the previous step. 
9. The user is then authenticated and logged in.


## Step 1: Create the Controller
Create a controller that extends the `Javaabu\Helpers\Http\Controllers\Controller` class, implements the `Javaabu\MobileVerification\Contracts\RegisterWithMobileNumberContract` interface and add `Javaabu\MobileVerification\Traits\RegistersWithMobileNumber` trait to it. Below is an example of how you can create the controller in your application.

```php
<?php

namespace Javaabu\MobileVerification\Tests\TestSupport\Controllers;

use Illuminate\Http\Request;
use Javaabu\Helpers\Http\Controllers\Controller;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;
use Javaabu\MobileVerification\Tests\TestSupport\Models\User;
use Javaabu\MobileVerification\Traits\RegistersWithMobileNumber;
use Javaabu\MobileVerification\Contracts\RegisterWithMobileNumberContract;

class RegisterController extends Controller implements RegisterWithMobileNumberContract
{
    use RegistersWithMobileNumber;
    
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
    
    /*
     * Implement the logic to register the user
     * */
    public function createUser(array $data): HasMobileNumber
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return $user;
    }
    
    /*
     * You are required to implement the getRegisterFieldsValidationRules method to return the validation rules
     * for the fields you want to validate during registration. By default, the package validates  
     * the country_code, number, verification code and the verification code id.
     * */
    public function getRegisterFieldsValidationRules(Request $request): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        ];
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
     * and the user is registered. For example, the dashboard URL
     * */
    public function verificationCodeSuccessRedirectUrl(): string
    {
        // TODO: Implement verificationCodeSuccessRedirectUrl() method.
    }

    /*
     * Provide the view to be used for the registration form, required fields
     * for this form is documented under "Making the requests" section
     * */
    public function getRegistrationFormView(): string
    {
        // TODO: Implement getRegistrationFormView() method.
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
    Route::get('/register', [RegisterController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.register.create');
    
    /*
     * This route will handle the request and send the verification code
     * if the mobile number is valid and not registered and shows a 
     * form to enter the verification code and other registration 
     * details, such as name, email, etc. This view is defined
     * in the `getRegistrationFormView` method in the controller.
     * */
    Route::post('/register', [RegisterController::class, 'requestVerificationCode'])->name('mobile-verifications.register.store');
    
    /*
     * This route will handle the validation of the verification code sent from the
     * previous route and other registration details and register the user using
     * `createUser` method in the controller, if the verification code and other
     * details are valid. The user is then authenticated and logged in.
     * */
    Route::match(['PATCH', 'PUT'],'/register', [RegisterController::class, 'register'])->name('mobile-verifications.register.update');

});
```

## Step 3: Making The Requests

### Requesting the OTP
Below is an example of how you can make the request in your application.

| Attribute    | Description                                                                         | Type   | Required |
|--------------|-------------------------------------------------------------------------------------|--------|----------|
| country_code | The country code of the mobile number. When not given, default country code is used | String | No       |
| number       | The mobile number to validate.                                                      | String | Yes      |


### Requesting To Register
Below are the default required and optional fields for the registration form. You may add more fields to the registration form, however the validation rules for any additional fields must be defined in the `getRegisterFieldsValidationRules` method in the controller.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code      | The country code of the mobile number. When not given, default country code is used              | String | No       |
| number            | The mobile number to validate.                                                                   | String | Yes      |
| verification_code | The OTP sent to the mobile number.                                                               | String | Yes      |
| verification_code_id | The ID of the verification code. This is automatically sent to the verification code entry form. | String | Yes      |





