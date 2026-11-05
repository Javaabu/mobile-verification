---
title: Requirements
sidebar_position: 1.1
---

- PHP 8.4 or 8.5
- Laravel 12 or 13
- [javaabu/auth](https://github.com/Javaabu/auth) v3, set up with at least one user type

It installs:

- [javaabu/auth](https://github.com/Javaabu/auth) v3 for user types, login and two-factor authentication
- [javaabu/sms-notifications](https://github.com/Javaabu/sms-notifications) to send the codes
- [javaabu/forms](https://github.com/Javaabu/forms) v2 for the Blade pages, [javaabu/stencil](https://github.com/Javaabu/stencil) for the registration form, and [javaabu/manifest](https://github.com/Javaabu/manifest) to ship the React pages
- [javaabu/permissions](https://github.com/Javaabu/permissions) v3 for the admin permissions

Optional, each detected when installed:

| Package | For |
| --- | --- |
| `inertiajs/inertia-laravel` ^2.0 or ^3.0 | The Inertia React pages |
| `laravel/sanctum` ^4.0 | Personal access tokens from `Route::mobileApiRoutes()` |
| `laravel/passport` ^13.0 | Personal access tokens, and the `mobile` OAuth2 grant |

To send real SMSs, install the channel of your provider, as javaabu/sms-notifications explains: `dash8x/dhiraagu-sms-notification` for Dhiraagu or `laravel-notification-channels/twilio` for Twilio.

The React pages need `react` and `react-dom` 18.3 or 19, `@inertiajs/react` 2 or 3, `@javaabu/forms` and `@javaabu/onu` in the app. `php artisan manifest:check` lists what's missing.
