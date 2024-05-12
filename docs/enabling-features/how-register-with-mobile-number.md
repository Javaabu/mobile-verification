---
title: How to Register with Mobile Number
sidebar_position: 1.7
---

# How to Register with Mobile Number
You can use this package to register users with their mobile numbers. Before diving into the steps, let's see how the registration process works.

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

## Step 1: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;

Route::post('validate', [App\Http\Controllers\MobileNumberVerificationController::class, 'validate'])->name('mobile-numbers.validate');
```

And if you are not doing an API request, you can define the route from which the request is coming from.
```php
use Illuminate\Support\Facades\Route;

Route::get('validate-form', [App\Http\Controllers\MobileNumberVerificationController::class, 'show'])->name('mobile-numbers.validate.show');
```

## Step 2: Create the Controller
Create a controller that extends the `Javaabu\MobileVerification\Http\Controllers\MobileNumberVerificationController` class. Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Javaabu\MobileVerification\Http\Controllers\MobileNumberVerificationController as BaseMobileNumberVerificationController;

class MobileNumberVerificationController extends BaseMobileNumberVerificationController
{
    /*
     * Define the user class to be used for mobile number validation
     */
    public string $user_class = User::class;
    
    /*
     * When not using an API request, define the view to be used for mobile number validation
     */
    public string $form_view = 'web.mobile-numbers.validate-form';
    
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
    public function redirectUrlOnValidationError(): RedirectResponse|JsonResponse|View
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
        return view('web.mobile-numbers.validate-form', ['custom_value' => 'custom_value']);
    }
```

## Step 3: Make the Request
Make a POST request to the route you defined in step 1 with the mobile number you want to validate. Below is the attributes you can send in the request.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code | The country code of the mobile number. | String | Yes |
| number | The mobile number to validate. | String | Yes |






