
# Requirements
- Opt-in recaptcha

## Verify Mobile Number Availability
- Need to know user type

### Workflow
- A guest user will enter their mobile number
- The system will check if the mobile number is already registered
- If the mobile number is already registered, the system will return a message that the mobile number is already registered

## Registration With Mobile Number
- Should not be logged in
- Need to know user type

### Workflow
- A guest user will enter their mobile number
- The system will check if the mobile number is already registered
- If the mobile number is already registered, the system will return a message that the mobile number is already registered
- If the mobile number is not registered, the system will send an OTP to the mobile number
- The user will enter the OTP (and any other details required)
- The system will verify the OTP (and the developer will verify the other details and register the user)

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





