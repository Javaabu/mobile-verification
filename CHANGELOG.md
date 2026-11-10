# Changelog

All notable changes to `javaabu/mobile-verification` are documented in this file.

## 3.0.0 - unreleased

A rewrite on top of javaabu/auth v3. See `docs/upgrading.md`.

- PHP 8.4+ and Laravel 12+; javaabu/auth v3, javaabu/forms v2, javaabu/stencil, javaabu/permissions v3 and javaabu/manifest. javaabu/helpers, javaabu/activitylog and javaabu/laravel-recaptcha dropped; Passport optional
- Mobile login as a javaabu/auth login method of any user type, finishing with `StartAuthenticatedSession` (two-factor still applies; `skip_two_factor`)
- Mobile registration with a javaabu/stencil details form and a `CreatesUsersFromMobileNumbers` action
- `Route::mobileNumberRoutes()` account page, linked from javaabu/auth's account navigation, and the `mobile.verified` middleware
- `Route::mobileApiRoutes()` for Sanctum/Passport tokens; the Passport `mobile` grant picks the user type from the client's provider
- `Route::mobileNumberActions()` admin actions behind `MobileNumberPolicy` and per-type javaabu/permissions permissions
- Blade and Inertia React pages; Dhivehi and RTL. The npm packages are declared for the pages only, so `manifest:check` doesn't ask Blade-only apps for React
- Codes from `random_int()`, configurable length, superseded by newer codes, named rate limiters, `request_code_rules`
- One `VerificationCodeNotification`, translated
- Docs, `llms.txt`, Laravel Boost guideline and skill
