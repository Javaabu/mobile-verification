{{--
    javaabu/mobile-verification guidelines for AI code assistants

    Maintained by: Javaabu (https://javaabu.com)
--}}

## javaabu/mobile-verification

- This project logs users in, signs them up and verifies their mobile numbers with SMS codes from `javaabu/mobile-verification`, on top of `javaabu/auth` user types. Mobile login is the `Javaabu\MobileVerification\Auth\MobileLoginMethod` login method of a user type; `Route::authRoutes()` registers its routes (`login.mobile`, `register.mobile`, with the type's prefix).
- Don't write OTP or mobile login controllers by hand, compare codes yourself or call `Auth::login()`: use the package's flows, `SendVerificationCode` / `VerifyVerificationCode`, and javaabu/auth's `StartAuthenticatedSession` (two-factor still applies).
- Always activate the `javaabu-mobile-verification` skill when turning on mobile login or registration for a user type, changing users' mobile numbers, sending or checking verification codes, protecting pages with `mobile.verified`, customising the SMS, the registration form or the pages, or issuing API tokens for a number and code.
- Numbers belong to users through `InteractsWithMobileNumbers` (`$user->phone`, `$user->formatted_mobile_number`); change them with `updateMobileNumber()` or on the `mobile-number` account page (`Route::mobileNumberRoutes()`).
