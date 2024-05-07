
# Requirements
- Opt-in recaptcha

## Verify Mobile Number Availability
- Need to know user type

### Workflow
- [x] A guest user will enter their mobile number
- [x] The system will check if the mobile number is already registered
- [x] If the mobile number is already registered, the system will return a message that the mobile number is already registered
- [x] If the mobile number is not registered, the system will return a message that the mobile number is available

## Registration With Mobile Number
- Should not be logged in
- Need to know user type

### Workflow
- [x] A guest user will enter their mobile number
- [x] The system will check if the mobile number is already registered
- [x] If the mobile number is already registered, the system will return a message that the mobile number is already registered
- [x] If the mobile number is not registered, the system will send an OTP to the mobile number
- [ ] The user will enter the OTP (and any other details required)
- [ ] The system will verify the OTP (and the developer will verify the other details and register the user)

## Mobile Number Login
- Should not be logged in
- Need to know user type

### Workflow
- A guest user should have an OTP
- The user will enter the OTP
- The system will verify the OTP and log in the user

## Mobile Number Update
- User needs to be logged in to update mobile number
- Request for an OTP to be sent to the new mobile number
- You cannot request for an OTP for an already existing number(with a user_id)
- When you request for an OTP, a new record will be created but will not be associated with any user_id, so that another user can take the number with verification

### Workflow
- Request for OTP
- Verify OTP, after verification the record will be associated with the user_id

Sending OTP
- Send to any number
- Send to a number that is not associated with any user_id
- Send to a number that is associated with a user_id

Verifying OTP
- Verify OTP for a number that is not associated with any user_id
- Verify OTP for a number that is associated with a user_id
- Verify OTP for any number

- In controller
- - one trait to send otp
- - base trait,

trait
SendsMobileVerificationCode;
- sendOtp to any number

LoginWithOtp
Add a grant type: (see: https://github.com/adaojunior/passport-social-grant)
MobileNumberGrant

For api, 
when a user sends their registration details with an otp,
provide a trait to register the user and return an access token
**Note:** Here the developer will control the end point and the controller, the given trait is implemented within the controller

UpdatesMobileNumber
SendMobileNumberForgotPasswordLink

## Controllers
- [x] Validating Mobile Number: ValidateMobileNumberControllerTest.php // This is for the mobile number availability check
- [ ] Registering with mobile number: RegisterControllerTest.php
- [ ] Updating mobile number: MobileNumberUpdateControllerTest.php
- [ ] Sending OTP: SendTokenControllerTest.php
- [ ] Verifying OTP: VerifyTokenControllerTest.php
