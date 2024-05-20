
# How to Request for OTP
When you are using this package you may need to request an OTP for a user. Below are times when you would need to request an OTP:
1. When a user is registering with their mobile number.
2. When a user is logging in with their mobile number.
3. When a user is updating their mobile number.

## OTP Request Process
Before diving into the steps, let's see how the OTP request process works. OTP Request process differs based on the use case. 

Below are the steps for the registration process:
1. 

Below are the steps for the login process:
1. A user sends a request to the application with their mobile number.


Below are the steps for the update process:
1. An authenticated user sends a request to the application with their new mobile number.
2. The package checks if the mobile number is already registered.
    3. If the mobile number is already registered, the package returns a message that the mobile number is already registered.
    4. If the mobile number is not registered, the package sends an OTP to the new mobile number.

## Step 1: Define the Routes
Define the routes in your application. Below is an example of how you can define the routes in your application. Remember to auth protect the route if you are requesting an OTP to update the mobile number.

```php
use Illuminate\Support\Facades\Route;

Route::post('request-otp', [App\Http\Controllers\OTPController::class, 'requestVerificationCode'])->name('otp.request');
```

And if you are not doing an API request, you can define the route from which the request is coming from.

```php
use Illuminate\Support\Facades\Route;

Route::get('request-otp-form', [App\Http\Controllers\OTPController::class, 'showOtpRequestForm'])->name('otp.request.show');
```

## Step 2: Create the Controller
Create a controller that extends the `Javaabu\MobileVerification\Http\Controllers\OTPController` class. Below is an example of how you can create the controller in your application.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

use Javaabu\MobileVerification\Http\Controllers\OTPController as BaseOTPController;use Javaabu\SmsNotifications\Notifications\SendsSms;

class OTPController implements SendVerificationCodeContract
{
    use SendsVerificationCode;
    
    public function getUserClass(Request $request): string
    {
        return User::class;
    }    
    
    public function mustBeARegisteredMobileNumber(array $request_data): ?bool
    {
        return null;
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
        return back()->with(['message' => __('The mobile number is invalid')]);
    }
```

You can also override how the form view is rendered by overriding the `showOtpRequestForm` or `getFormView` method.

```php
    /*
     * Define showOtpRequestForm method to return a view
     * */
    public function showOtpRequestForm(): View
    {
        return view('web.otp.request-form');
    }
```

When you are trying to handle OTP requests for both registration and login, you can customize the `mustBeARegisteredMobileNumber` method to handle the request.
It should return true if the mobile number must be a registered mobile number (login process) and false if the mobile number must not be a registered mobile number (registration process).
```php
    public function mustBeARegisteredMobileNumber(array $request_data): bool
    {
        // ... your logic here
    }
```

## Step 3: Create the Form

If you are not doing an API request, you can create a form to request the OTP. Below is an example of how you can create the form in your application.

```html
<form action="{{ route('otp.request') }}" method="post">
    @csrf
    
    <!-- You may use your own select, and the country codes form the Countries enum provided by the package -->
    <input type="text" name="country_code" placeholder="Enter your country code">
    <input type="text" name="number" placeholder="Enter your mobile number">
    <button type="submit">Request OTP</button>
</form>
```

## Step 4: Make the Request
Make a POST request to the route you defined in step 1 with the mobile number you want to send the OTP to. Below is the attributes you can send in the request.

| Attribute | Description | Type   | Required |
| --- | --- |--------| --- |
| country_code | The country code of the mobile number. | String | Yes |
| number | The mobile number to validate. | String | Yes |


