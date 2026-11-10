---
title: The user model
sidebar_position: 1
---

A user model with a mobile number implements `Javaabu\MobileVerification\Contracts\HasMobileNumber` and uses `Javaabu\MobileVerification\Concerns\InteractsWithMobileNumbers`:

```php
namespace App\Models;

use Javaabu\Auth\Models\User as BaseUser;
use Javaabu\MobileVerification\Concerns\InteractsWithMobileNumbers;
use Javaabu\MobileVerification\Contracts\HasMobileNumber;

class Admin extends BaseUser implements HasMobileNumber
{
    use InteractsWithMobileNumbers;
}
```

A user has at most one verified number, the `phone` relation (a `MorphOne` to `Javaabu\MobileVerification\Models\MobileNumber`). A number record without a user is a number someone asked a code for but hasn't verified, or one a user gave up.

# Reading the number

```php
$user->phone;                       // ?MobileNumber
$user->hasVerifiedMobile();         // bool
$user->formatted_mobile_number;     // '+9607771234'
$user->mobile_number;               // '7771234'
$user->mobile_number_country_code;  // '960'
$user->short_mobile_number;         // '7771234', or '+947712345' for other country codes
$user->needs_phone_verification;    // no verified number, and shouldVerifyPhone()
```

Override `shouldVerifyPhone()` for users who don't need a number (the `mobile.verified` middleware lets them through):

```php
public function shouldVerifyPhone(): bool
{
    return ! $this->is_service_account;
}
```

# Finding users

```php
User::findByMobileNumber('7771234');                // the default country code
User::findByMobileNumber('771234567', '94');
User::query()->hasMobileNumber('7771234', '960')->get();
User::query()->hasShortMobileNumber('+9607771234')->get();   // as users write it
User::query()->hasPhone('777')->get();                       // a search
User::query()->phoneVerified()->get();
User::query()->phoneUnverified()->get();
```

# Changing the number in code

These skip the verification code, for imports, seeders and admin tools. Users change their own number on the [account page](verifying-numbers.md), which checks a code.

```php
$user->updateMobileNumber('9991234');                   // null if another user of the same type has it
$user->updateMobileNumber('771234567', '94', causer: $admin);
$user->updatePhone($mobile_number);                     // a MobileNumber, or its id
$user->removeMobileNumber(causer: $admin);
```

Each fires `Javaabu\MobileVerification\Events\MobileNumberUpdated` with the old and new number, the user and the causer. Force deleting a user deletes their number; soft deleting keeps it, so a restored user still has it.

# SMS notifications

The trait routes the `sms` channels of javaabu/sms-notifications (`routeNotificationForSms()`, `routeNotificationForDhiraagu()`, `routeNotificationForTwilio()`) to the verified number, so any SMS notification works on the user:

```php
$user->notify(new OrderShippedSms($order));
```

# The MobileNumber model

```php
use Javaabu\MobileVerification\MobileVerification;

$mobile_number = MobileVerification::find('user', '960', '7771234');   // ?MobileNumber, for a user type
$mobile_number->formatted_number;        // '+9607771234'
$mobile_number->user;                    // the owner, or null
$mobile_number->hasOwner();
$mobile_number->isLocked();
$mobile_number->lockExpiresIn();         // seconds
$mobile_number->wasSentRecently();
$mobile_number->resendVerificationCodeIn(); // seconds
$mobile_number->isVerificationCodeExpired();
$mobile_number->resetAttempts();         // unlock
```

Numbers are normalized when saved: `077 712-34` becomes `7771234`, and `+960` becomes `960`. The `user_type` column holds the user model's morph class. Replace the model with `mobile-verification.mobile_number_model` (it must extend the package's); the table and connection come from `table_name` and `database_connection`. A factory ships for tests:

```php
MobileNumber::factory()->forUser($user)->create(['number' => '7771234']);
MobileNumber::factory()->forUserType((new User)->getMorphClass())->locked()->create();
```
