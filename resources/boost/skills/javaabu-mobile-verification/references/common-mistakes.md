# Common mistakes

## Writing the flow by hand

```php
// Wrong: plain codes, no attempts, no lock, skips two-factor
$code = rand(100000, 999999);
session(['otp' => $code]);
if ($request->code == session('otp')) {
    Auth::login(User::where('mobile', $request->number)->first());
}
```

Turn on `MobileLoginMethod` for the type. For your own flows, use `SendVerificationCode` and `VerifyVerificationCode`, and finish logins with javaabu/auth's `StartAuthenticatedSession`.

## Checking a code in a validation rule

A wrong code counts as an attempt and can lock the number; a rule that runs twice, or on a request that fails for another field, burns attempts. Validate the fields, then call `VerifyVerificationCode`.

## Assuming one kind of user

```php
// Wrong: admins and users can share a number, and the guard may not be `web`
MobileNumber::where('number', $number)->first()->user;
Auth::guard('web')->login($user);
```

```php
use Javaabu\MobileVerification\MobileVerification;

$type = UserTypes::get('admin');
$user = MobileVerification::userFor($type, MobileVerification::find($type, $country_code, $number));
```

## Hard-coding routes

`route('login.mobile')` is the default type's; other types have a prefix (`admin.login.mobile`). Use `$type->route('login.mobile')` and `$type->route('mobile-number')`.

## Registering routes before the login method

`MobileVerification::enableFor()` (or `UserTypes::get(...)->loginMethod(...)`) must run before `Route::authRoutes()`. Config (`login_methods`) always does.

## Required password or email columns

Mobile registration creates users without a password, and the default form doesn't require an email. Make those columns nullable, or change the form and the `CreatesUsersFromMobileNumbers` action.

## Forgetting the account status

New users get javaabu/auth's default status, `pending`. Approve them in your `CreatesUsersFromMobileNumbers` action if they may use the app at once.

## Physical CSS and reversed numbers

Use `ms-`/`me-`/`text-start`, and keep numbers `dir="ltr"` (wrap them in `<bdi dir="ltr">` inside Dhivehi text) so `+960` stays in front.
