---
title: Events
sidebar_position: 5
---

| Event | When | Properties |
| --- | --- | --- |
| `Events\VerificationCodeSent` | A code was sent | `mobile_number`, `purpose` (`Enums\Purposes`) |
| `Events\VerificationCodeFailed` | A code was wrong, expired, for an older code, or the number was locked | `mobile_number`, `reason` (`incorrect`, `expired`, `mismatch`, `locked`) |
| `Events\MobileNumberLocked` | A wrong code locked the number | `mobile_number` |
| `Events\MobileNumberUpdated` | A user's verified number changed or was removed | `old_number`, `new_number` (formatted, or null), `user`, `causer` |

The events never carry the code. javaabu/auth's and Laravel's events fire as usual: `Login` after the mobile login, `Registered` after the mobile registration, and `TwoFactorAuthenticationChallenged` when the two-factor challenge follows.

Log number changes with spatie/laravel-activitylog, for example:

```php
use Illuminate\Support\Facades\Event;
use Javaabu\MobileVerification\Events\MobileNumberUpdated;

Event::listen(function (MobileNumberUpdated $event) {
    activity()
        ->performedOn($event->user)
        ->causedBy($event->causer)
        ->withProperties(['old' => $event->old_number, 'new' => $event->new_number])
        ->log('mobile_number_updated');
});
```
