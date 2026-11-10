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

# The account navigation

The package adds "Mobile number" to javaabu/auth's account navigation, after Profile and Security (position 30), for every user type that has the page: the types you called `Route::mobileNumberRoutes()` for. javaabu/auth's account layout and its React `AccountLayout` show it, marked as the current page while users are on it. If your app has its own account layout, include auth's navigation there and the link comes with it:

```blade
@include('auth::partials.account-nav')
```

Move the link, rename it or remove it per type with javaabu/auth's API:

```php
use Javaabu\Auth\UserTypes;

UserTypes::get('admin')->accountLink('mobile-number', 'Phone', position: 15);
UserTypes::get('user')->withoutAccountLink('mobile-number');
```

Its label is the English key "Mobile number" (Dhivehi in the package's `lang/dv.json`).

# Requiring a verified number

`mobile.verified:{type}` sends signed-in users without a verified number to the type's `mobile-number` page, then back to where they were going once the code is entered. JSON requests get a `403`. Put it after `auth.type`:

```php
Route::middleware(['auth.type:user', 'mobile.verified:user'])->group(function () {
    Route::get('orders', [OrdersController::class, 'index'])->name('orders.index');
});
```

Without a parameter it uses the route's user type, then the default one. Users whose `shouldVerifyPhone()` returns false pass. `mobile-verified`, the v2 name, still works.
