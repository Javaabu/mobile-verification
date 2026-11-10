---
title: API
sidebar_position: 5
---

Mobile apps and other clients without a session trade a number and its code for a token.

# Personal access tokens (Sanctum or Passport)

```php
// routes/api.php
Route::mobileApiRoutes('user', ['prefix' => 'api', 'middleware' => ['api']]);
```

| Method | URL | Name | |
| --- | --- | --- | --- |
| POST | `api/mobile/code` | `api.mobile.code` | Send a login code |
| POST | `api/mobile/token` | `api.mobile.token` | A token for the number and code |

Names get the type's prefix (`admin.api.mobile.code`). The token comes from javaabu/auth's `ApiTokens`, so the user model needs Sanctum's or Passport's `HasApiTokens`, and `javaabu-auth.api` must allow that driver (`php artisan auth:install --api=sanctum`).

```http
POST /api/mobile/code
{"number": "7771234", "country_code": "960"}

200 {"verification_code_id": "9b1c…", "formatted_number": "+9607771234", "expires_in": 600, "resend_in": 30, …}

POST /api/mobile/token
{"number": "7771234", "verification_code": "123456", "verification_code_id": "9b1c…", "device_name": "Aisha's iPhone"}

201 {"token": "1|q4Tb…", "token_type": "Bearer"}
```

`verification_code_id` ties the token request to the code the client asked for. Users with two-factor authentication also send `code` (from their authenticator app) or `recovery_code`, unless `skip_two_factor` is on. Pending and banned accounts get no token. Errors are `422` with Laravel's validation format, and `429` for locked numbers and resend limits.

# The Passport `mobile` grant

With laravel/passport installed, the package adds a `mobile` grant to Passport's token endpoint, for apps that use OAuth2 clients:

```http
POST /oauth/token
grant_type=mobile&client_id=…&client_secret=…&number=7771234&country_code=960&verification_code=123456&scope=
```

```json
{"token_type": "Bearer", "expires_in": 31536000, "access_token": "…", "refresh_token": "…"}
```

- Clients need the `mobile` grant, or the password grant: `php artisan passport:client --password --provider=users`.
- The client's provider picks the user type: the type whose guard uses that provider. Clients of other providers get `invalid_client`.
- Send the login code first, with `POST login/mobile` (JSON) or `POST api/mobile/code`. `verification_code_id` is optional here.
- Two-factor users add `code` or `recovery_code`.
- Turn the grant off with `'passport_grant' => false`.

Errors are OAuth2 errors: `invalid_request` with the message of the failing field (`hint`).
