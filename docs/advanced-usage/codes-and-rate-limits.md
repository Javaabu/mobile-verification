---
title: Codes and rate limits
sidebar_position: 2
---

# Codes

- Codes are `code_length` random digits (6 by default) from `random_int()`, a cryptographically secure source.
- They're stored hashed (`Hash::make()`), never in plain text, and left out of the model's array and JSON.
- A code expires `verification_code_validity` minutes (10) after it's sent, and is used up by the right entry, so it can't be entered twice.
- Each code has a `verification_code_id` (a UUID). The web flows keep it in the session, and the API sends it back; a code sent later (to another browser or app) makes the earlier one invalid.
- Requesting a new code makes the old one invalid.

# Attempts and locks

Each wrong code counts against the number, whichever browser it came from. After `max_attempts` (5) the number is locked: no code is accepted, not even the right one, and no new code is sent, until `attempt_expiry` minutes (30) after the last code was sent. `MobileNumberLocked` fires when that happens. Admins unlock numbers with the [unlock action](../basic-usage/managing-numbers.md), and code calls `$mobile_number->resetAttempts()`.

A number gets one code per `resend_interval` seconds (30).

# Rate limits

On top of the per-number limits, two named rate limiters guard the routes:

| Limiter | Default | Key | Routes |
| --- | --- | --- | --- |
| `mobile-verification.send` | 5 per minute | user type, IP | sending and resending codes |
| `mobile-verification.verify` | 10 per minute | user type, IP, session | entering codes, getting API tokens |

Change the numbers in `rate_limits`, or redefine a limiter in a service provider's `boot()` (after the package's):

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('mobile-verification.send', fn (Request $request) => [
    Limit::perMinute(3)->by($request->ip()),
    Limit::perDay(20)->by($request->ip()),
]);
```

# Status codes for JSON

| Case | Status |
| --- | --- |
| Validation errors, wrong or expired codes | 422 |
| Locked number, resend too soon | 429, with the message on `number` or `verification_code` |
| Rate limiter | 429 |
| A signed-in user on a guest route (`auth.guest`) | 409 |

# Using the actions directly

```php
use Javaabu\Auth\UserTypes;
use Javaabu\MobileVerification\Actions\SendVerificationCode;
use Javaabu\MobileVerification\Actions\VerifyVerificationCode;
use Javaabu\MobileVerification\Enums\Purposes;

$type = UserTypes::get('user');

// Throws a ValidationException (on `number`) when locked, sent recently, or not allowed for the purpose
$mobile_number = app(SendVerificationCode::class)($type, Purposes::LOGIN, '960', '7771234');

// Throws a ValidationException (on `verification_code`); the right code is used up
app(VerifyVerificationCode::class)($mobile_number, '123456', $mobile_number->verification_code_id);
```

`Purposes::LOGIN` sends to numbers a user of the type has verified, `REGISTER` and `UPDATE` to numbers no other user of the type has (for `UPDATE`, pass the user as the fifth argument).
