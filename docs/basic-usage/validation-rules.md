---
title: Validation rules
sidebar_position: 8
---

# IsValidMobileNumber

A number in the right format for its country code, read from the `country_code` input (or the default country code):

```php
use Javaabu\MobileVerification\Rules\IsValidCountryCode;
use Javaabu\MobileVerification\Rules\IsValidMobileNumber;

$request->validate([
    'country_code' => ['nullable', 'string', new IsValidCountryCode],
    'number' => ['required', 'string', new IsValidMobileNumber],
]);
```

Also check whether it's a user's verified number, for a javaabu/auth user type:

```php
(new IsValidMobileNumber('user'))->registered();        // must belong to a user
(new IsValidMobileNumber('user'))->notRegistered();     // must not belong to anyone else
(new IsValidMobileNumber('user'))->notRegistered()->ignore($user);   // except this user
(new IsValidMobileNumber(UserTypes::get('admin'), 'phone_country_code'));   // another country code input
(new IsValidMobileNumber(Admin::class, is_morph_class: true))->registered(); // a morph class instead of a type
```

The format check is `Javaabu\MobileVerification\Contracts\ValidatesNumberFormat`, by default 7 digits starting with 7 or 9 for +960, and 4 to 15 digits for other country codes. Bind your own, or set `number_format_validator`:

```php
use Javaabu\MobileVerification\Contracts\ValidatesNumberFormat;

class SriLankanNumbers implements ValidatesNumberFormat
{
    public function passes(string $country_code, string $number): bool
    {
        return match ($country_code) {
            '960' => (bool) preg_match('/^[79]\d{6}$/', $number),
            '94' => (bool) preg_match('/^7\d{8}$/', $number),
            default => false,
        };
    }
}
```

# IsValidCountryCode

One of `allowed_country_codes`, with or without the prefix (`960`, `+960`).

# Checking codes yourself

Codes aren't checked by a validation rule, because a wrong code counts as an attempt. Use the actions:

```php
use Javaabu\MobileVerification\Actions\SendVerificationCode;
use Javaabu\MobileVerification\Actions\VerifyVerificationCode;
use Javaabu\MobileVerification\Enums\Purposes;

$mobile_number = app(SendVerificationCode::class)($type, Purposes::UPDATE, '960', '7771234', $user);

// later; throws a ValidationException on `verification_code`
app(VerifyVerificationCode::class)($mobile_number, $request->input('verification_code'), $mobile_number->verification_code_id);
```
