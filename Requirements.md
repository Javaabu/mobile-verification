# Requirements

- Opt-in recaptcha

## Verify Mobile Number Availability [DOCUMENTED]

- Need to know user type

### Workflow

- [x] A guest user will enter their mobile number
- [x] The system will check if the mobile number is already registered
- [x] If the mobile number is already registered, the system will return a message that the mobile number is already
  registered
- [x] If the mobile number is not registered, the system will return a message that the mobile number is available

## Registration With Mobile Number [DOCUMENTED]

- Should not be logged in
- Need to know user type

### Workflow

- [x] A guest user will enter their mobile number
- [x] The system will check if the mobile number is already registered
- [x] If the mobile number is already registered, the system will return a message that the mobile number is already
  registered
- [x] If the mobile number is not registered, the system will send an OTP to the mobile number
- [x] The user will enter the OTP (and any other details required)
- [x] The system will verify the OTP (and the developer will verify the other details and register the user)

## Mobile Number Login [DOCUMENTED]

- Should not be logged in
- Need to know user type

### Workflow

- [x] A guest user should have an OTP
- [x] The user will enter the OTP
- [x] The system will verify the OTP and log in the user

## Mobile Number Update [DOCUMENTED]

- [x] User needs to be logged in to update mobile number
- [x] User can request for an OTP to be sent to the new mobile number
- [x] You cannot request for an OTP for an already existing number(with a user_id)
- [x] When you request for an OTP, a new record will be created but will not be associated with any user_id, so that
  another user can take the number with verification

### Workflow

- [x] Request for OTP
- [x] Verify OTP, after verification the record will be associated with the user_id

Sending OTP

- [x] Send to any number
- [x] Send to a number that is not associated with any user_id
- [x] Send to a number that is associated with a user_id

Verifying OTP

- [x] Verify OTP for a number that is not associated with any user_id
- [x] Verify OTP for a number that is associated with a user_id
- [x] Verify OTP for any number

- In controller
-
    - one trait to send otp
-
    - base trait,

trait
SendsMobileVerificationCode;

- sendOtp to any number

LoginWithOtp
Add a grant type: (see: https://github.com/adaojunior/passport-social-grant)
MobileNumberGrant

For api,
when a user sends their registration details with an otp,
provide a trait to register the user and return an access token
**Note:** Here the developer will control the end point and the controller, the given trait is implemented within the
controller

UpdatesMobileNumber
SendMobileNumberForgotPasswordLink

## Controllers

- [x] Validating Mobile Number: ValidateMobileNumberControllerTest.php // This is for the mobile number availability
  check
- [x] Registering with mobile number: RegisterControllerTest.php
- [x] Updating mobile number: MobileNumberUpdateControllerTest.php
- [x] Sending OTP: SendTokenControllerTest.php
- [x] Verifying OTP: TokenValidationControllerTest.php
- [x] Login In: TokenLoginControllerTest.php

Verification Code -> OTP
Token -> Access Token API
