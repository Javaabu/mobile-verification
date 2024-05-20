---
title: Mobile Number Validation Rule
sidebar_position: 2.2
---

# Mobile Number Validation Rule
The package provides a validation rule to validate mobile numbers. The following validations are performed on the mobile number:
1. The mobile number format is validated.
2. Optionally check if the mobile number is registered in the database.
3. Optionally check if the system can send a verification code to the mobile number.

## Usage

You can use the `mobile_number` validation rule to validate mobile numbers. Below is an example of how you can use the rule in your application:

```php
<?php

$rules = [
    'number' => [
        'required',
        (new IsValidMobileNumber(
            'user', // this is a required parameter that specifies the morph class of the user model
            'country_code' // this is an optional parameter you can pass if the country code input name is different from the default 'country_code'
        ))
            ->registered() // Check if the mobile number is registered
            ->canSendOtp() // Check if the system can send an OTP to the mobile number
    ]
];

```

### Other available Options
- You may also call the `notRegistered()` method to check if the mobile number is not registered in the database. 
- You may also call the `setShouldBeRegisteredNumber()` and pass in a boolean value to set the `registered` or `notRegistered()` method to be called based on the boolean value. This is useful when you want to dynamically set the validation rule based on a condition.

## Customizing Mobile Number Format Validation
The validation format is validated using another class defined in the configuration file. You can customize the validation format by creating a new class that implements the `Javaabu\MobileVerification\Contracts\IsANumberFormatValidator` interface and updating the configuration file to use the new class.

Below is an example of how you can create a new class to validate the mobile number format:

```php
<?php

namespace App\Support;

use Javaabu\MobileVerification\Contracts\IsANumberFormatValidator;

class MobileNumberFormatValidator implements IsANumberFormatValidator
{
    public function handle(string $country_code, string $number): bool
    {
        // Your custom validation logic here
        
        return true; // Return true if the mobile number is valid
    }
}
```

After creating the class, update the configuration file to use the new class:

```php
<?php
    /*
    |--------------------------------------------------------------------------
    | Mobile Number Format Validator
    |--------------------------------------------------------------------------
    | This class that will be used to validate the mobile number format.
     * */
    'mobile_number_format_validator' => \App\Support\MobileNumberFormatValidator::class,
```
