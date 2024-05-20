---
title: Verification Code Validation Rule
sidebar_position: 2.3
---

# Verification Code Validation Rule
The package provides a validation rule to validate verification code. The following validations are performed on the mobile number:
1. The verification code id provided with the verification code is validated.
2. Checks if the mobile number for the verification code is locked.
3. Checks if the verification code is expired.
4. Checks if the verification code is correct.

## Usage
You can use the `IsValidVerificationCode` validation rule to validate verification codes. Below is an example of how you can use the rule in your application:

```php
<?php

$rules = [
    //... other validation rules for number, country code and verification code id
    'number' => [
        'required',
        (new IsValidVerificationCode(
            'user', // required parameter that specifies the morph class of the user model
            'country_code', // optional parameter you can pass if the country code input name is different from the default 'country_code'
            'number', // optional parameter you can pass if the mobile number input name is different from the default 'number'
            'verification_code_id' // optional parameter you can pass if the verification code id input name is different from the default 'verification_code_id'
        ))
            /*
             * Optionally set whether the verification code should be cleared and 
             * verification attempts should be reset, (default is false)
             * */
            ->setShouldResetAttempts(true) 
    ]
];

```
