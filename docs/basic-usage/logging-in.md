---
title: Logging in
sidebar_position: 2
---

With `MobileLoginMethod` on a user type, its login page gets a "Log in with your mobile number" link (`data-login-method="mobile"`), and users log in in two steps:

1. **The number.** `GET login/mobile` asks for the number (and the country code, when more than one is allowed). `POST login/mobile` checks that a user of this type has verified it, sends a code by SMS, and remembers the number in the session.
2. **The code.** `GET login/mobile/verify` asks for the code. `POST login/mobile/verify` checks it, then hands over to javaabu/auth's `StartAuthenticatedSession`, exactly as the password form does: users with two-factor authentication get the challenge, otherwise they're logged in on the type's guard, the session is regenerated, and `LoginResponse` sends them to the intended page or the type's home.

`POST login/mobile/resend` sends another code once the resend interval has passed. "Remember me" on the number page is kept for the login.

# Routes

Registered by `Route::authRoutes()` in the type's guest group, so they have its prefix, middleware, `auth.guest:{type}` and route-name prefix (`admin.login.mobile`):

| Method | URL | Name | |
| --- | --- | --- | --- |
| GET | `login/mobile` | `login.mobile` | The number page |
| POST | `login/mobile` | `login.mobile.store` | Send a code (`throttle:mobile-verification.send`) |
| GET | `login/mobile/verify` | `login.mobile.verify` | The code page |
| POST | `login/mobile/verify` | `login.mobile.verify.store` | Check the code and log in (`throttle:mobile-verification.verify`) |
| POST | `login/mobile/resend` | `login.mobile.resend` | Send another code |

Leave them out for one registration with `'except' => ['login_method:mobile']`:

```php
Route::authRoutes('admin', ['prefix' => 'admin', 'except' => ['login_method:mobile']]);
```

# Two-factor authentication

A code by SMS replaces the password, not the second factor, so users who turned on two-factor authentication still enter their authenticator code. To let the mobile login skip it (when the SMS is your second factor):

```php
// config/mobile-verification.php
'skip_two_factor' => true,
```

# What can go wrong

| Situation | Message (on `number` or `verification_code`) |
| --- | --- |
| No user of this type has the number | There is no account with this mobile number. |
| Wrong format (Maldivian numbers are 7 digits starting with 7 or 9) | The mobile number is not a valid mobile number. |
| A code was sent less than 30 seconds ago | A code was sent to this number recently. You can ask for another in :seconds seconds. |
| Wrong code | The code is incorrect. You have :count attempts left. |
| 5 wrong codes | This number is locked after too many wrong codes. Try again in :minutes minutes. |
| Code older than 10 minutes | The code has expired. Ask for a new code. |
| A newer code was sent (from another browser) | This code is no longer valid. Ask for a new code. |
| The session lost the number | Enter your mobile number again to get a new code. |

Locks and resend limits answer `429` to JSON requests; see [Codes and rate limits](../advanced-usage/codes-and-rate-limits.md).

The number page says when a number has no account. That tells visitors which numbers are registered, as the registration page must anyway; the rate limits keep it slow.

# JSON

Every step answers JSON when the request expects it, for single-page apps that use the session:

```http
POST /login/mobile
{"number": "7771234"}

200 {"formatted_number": "+9607771234", "verification_code_id": "…", "is_registered": true,
     "expires_in": 600, "resend_in": 30, "remaining_attempts": 5, "is_locked": false, …}

POST /login/mobile/verify
{"verification_code": "123456"}

200 {"two_factor": false, "redirect": "https://example.com/dashboard"}
```

Mobile apps without a session use the [API](api.md) instead.
