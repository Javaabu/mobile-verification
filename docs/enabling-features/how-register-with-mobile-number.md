---
title: How to Register with Mobile Number
sidebar_position: 1.7
---

# How to Register with Mobile Number
You can use this package to register users with their mobile numbers. Before diving into the steps, let's see how the registration process works.

## Registration Process
The registration process with mobile number works as follows:
**Note:** The process assumes that the OTP has been sent to the user's mobile number. To see how to send an OTP, see [How to Request for OTP](how-to-request-otp.md).
1. The user then enters the OTP and any other details required for registration.
2. The package verifies the OTP.
3. The developer can then implement the logic to verify the other details and user registration.
4. The package will then assign the verified mobile number to the user registered in the previous step.
5. The user is then authenticated and logged in.

## Step 1: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;

Route::post('register', [RegisterController::class, 'register'])->name('mobile-numbers.register');
```

And if you are not doing an API request, you can define the route from which the request is coming from.
```php
use Illuminate\Support\Facades\Route;

Route::get('registration-form', [RegisterController::class, 'showRegistrationForm'])->name('mobile-numbers.register.show');
```

## Step 2: Create the Controller
Create a controller that extends the `Javaabu\MobileVerification\Http\Controllers\RegistrationController` class and implements the `IsRegistrationController` interface.
Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\MobileVerification\Http\Controllers\RegistrationController as BaseRegistrationController;

class RegistrationController extends BaseRegistrationController implements IsRegistrationController
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
     * When not using an API request, you can define the view to be used for mobile number validation
     */
    public string $form_view = 'web.mobile-numbers.registration-form';
    
    /*
     *  Implement the logic to register the user
     * */
    public function registerUser(array $data): HasMobileNumber
    {
        // Implement the logic to register the user
    }
    
    /*
     * You are required to implement the getUserDataValidationRules method to return the validation rules
     * for any custom data you want to validate other than the country_code, number and the token.
     * */
    public function getUserDataValidationRules(array $request_data): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email'],
        ];
    }
    
    /*
     * Define redirectUrl method to return a redirect response or a json response
     * */
    public function redirectUrl(): RedirectResponse|JsonResponse|View
    {
        return to_route('web.home')->with(['message' => __('The mobile number is valid')]);
    }
}
```

You also have the option to override the `redirectUrlOnValidationError` method to return a redirect response or a json response when the mobile number is invalid.

```php
    /*
     * Define redirectUrlOnValidationError method to return a redirect response or a json response
     * */
    public function redirectUrlOnValidationError(Request $request, \Illuminate\Validation\Validator $validator): RedirectResponse|JsonResponse|View
    {
        return back()->withErrors(['message' => __('The mobile number is invalid')]);
    }
```

You can also override the view method used to render the view from which the request is coming from.

```php
    /*
     * Define the view method to be used for mobile number validation
     */
    public function getView(): View
    {
        return view('web.mobile-numbers.registration-form', ['custom_value' => 'custom_value']);
    }
```

## Step 3: Make the Request
Make a POST request to the route you defined in step 1 with the mobile number you want to validate. Below is the attributes you are expected to send in the request.
You may also send any other data required for registration. Any other data sent will be validated using the `getUserDataValidationRules` method.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code | The country code of the mobile number. | String | Yes |
| number | The mobile number to validate. | String | Yes |
| token | The OTP sent to the mobile number. | String | Yes |






