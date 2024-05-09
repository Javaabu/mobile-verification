---
title: How to Enable Mobile Number Validation
sidebar_position: 1.5
---

# How to Enable Mobile Number Validation
The package does not register its own routes. Therefore, you must define the routes in your application. The package provides a base abstract controller that you can extend to create your own controller.

## Step 1: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application.

```php
use Illuminate\Support\Facades\Route;

Route::post('validate', [App\Http\Controllers\MobileNumberVerificationController::class, 'validate'])->name('mobile-numbers.validate');
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

## Step 3: Make the Request
Make a POST request to the route you defined in step 1 with the mobile number you want to validate. Below is the attributes you can send in the request.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code | The country code of the mobile number. | String | Yes |
| number | The mobile number to validate. | String | Yes |






