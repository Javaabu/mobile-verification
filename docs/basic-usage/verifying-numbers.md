---
title: Verifying and changing numbers
sidebar_position: 4
---

Signed-in users verify a number, or change theirs, on an account page. Register it per user type:

```php
Route::mobileNumberRoutes('user');
Route::mobileNumberRoutes('admin', [
    'prefix' => 'admin/settings',
    'middleware' => ['web'],
    'confirm_password' => true,
]);
```

| Option | Default | What |
| --- | --- | --- |
| `prefix` | `settings` | URL prefix |
| `middleware` | `['web']` | Middleware before `auth.type:{type}`, which the routes always have |
| `domain` | none | Domain of the routes |
| `confirm_password` | `false` | Ask for the password first (`auth.password.confirm:{type}`). Leave it off for types whose users may have no password |

| Method | URL | Name | |
| --- | --- | --- | --- |
| GET | `settings/mobile-number` | `mobile-number` | The current number and a form for a new one |
| POST | `settings/mobile-number` | `mobile-number.store` | Send a code to the new number |
| GET, POST | `settings/mobile-number/verify` | `mobile-number.verify`, `mobile-number.verify.store` | The code; the new number replaces the old one |
| POST | `settings/mobile-number/resend` | `mobile-number.resend` | Another code |

Names get the type's prefix: `admin.mobile-number`. The pages use the type's `account` layout (javaabu/auth's `AccountLayout` in React). The old number stays until the code for the new one is entered; then `MobileNumberUpdated` fires with the user as the causer.

A number another user of the same type has can't be taken ("This mobile number is already registered."). Users of different types can share a number.

Link to the page from your account navigation:

```blade
<a href="{{ $user_type->route('mobile-number') }}">{{ __('Mobile number') }}</a>
```

# Requiring a verified number

`mobile.verified:{type}` sends signed-in users without a verified number to the type's `mobile-number` page, then back to where they were going once the code is entered. JSON requests get a `403`. Put it after `auth.type`:

```php
Route::middleware(['auth.type:user', 'mobile.verified:user'])->group(function () {
    Route::get('orders', [OrdersController::class, 'index'])->name('orders.index');
});
```

Without a parameter it uses the route's user type, then the default one. Users whose `shouldVerifyPhone()` returns false pass. `mobile-verified`, the v2 name, still works.
