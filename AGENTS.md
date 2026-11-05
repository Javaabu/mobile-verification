# mobile-verification: notes for AI coding agents

`javaabu/mobile-verification` v3 adds mobile number login, registration and verification with SMS codes to javaabu/auth v3 user types. It plugs in through javaabu/auth's `LoginMethod` and `StartAuthenticatedSession`, so treat those (and `UserTypes`, `PageData`, `ApiTokens`, `AccountLayout`) as the contract with auth; if auth lacks something, open a `cross-package` issue on n4ff4h/auth instead of working around it.

## Layout

| Path | What |
| --- | --- |
| `src/MobileVerification.php` | Static helpers: config, normalizing, finding numbers per user type, `enableFor()` |
| `src/Auth/MobileLoginMethod.php` | The javaabu/auth login method; registers the login and registration routes |
| `src/Routing` | `Route::mobileNumberRoutes()`, `Route::mobileApiRoutes()`, `Route::mobileNumberActions()` |
| `src/Http/Controllers` | `VerificationFlowController` (the shared send/verify/confirm/resend steps) and the login, registration and account flows; the API and admin actions controllers |
| `src/Actions` | `SendVerificationCode`, `VerifyVerificationCode`, `AuthenticateMobileNumber` (API/Passport), `CreateUserFromMobileNumber` |
| `src/Models/MobileNumber.php`, `src/Concerns/InteractsWithMobileNumbers.php` | The number (code, expiry, attempts, lock) and the user trait |
| `src/Support` | `PendingVerification` (session state per type and purpose), `Strings` (React strings), the code generator, the number format validator |
| `src/Passport/MobileGrant.php` | The optional `mobile` grant |
| `src/Policies/MobileNumberPolicy.php` | Admin abilities and permission names |
| `src/Stencil/MobileRegistrationForm.php` | The registration details form |
| `src/Views/ViewRenderer.php` | Blade (`mobile-verification::login`) or Inertia (`mobile-verification::Login`) |
| `resources/views`, `resources/js` | Blade pages (javaabu/forms v2) and React pages (`@javaabu/forms`, imports `@javaabu/auth` and `@javaabu/stencil` from vendor/) |
| `lang/en.json`, `lang/dv.json` | English keys and Dhivehi |
| `docs`, `llms.txt`, `resources/boost` | Docusaurus pages, the index for AI assistants, the Boost guideline and skill |
| `tests` | Testbench tests with two types: `user` (`web`, no route prefix, registration on) and `admin` (`web_admin`, `/admin`, registration off). Inertia, Sanctum and Passport tests are in the `optional` group. Seconds are frozen in `TestCase`. `tests/js` are Vitest tests |

## Rules

- Follow Javaabu codestyle ([boost-javaabu-guidelines](https://github.com/Javaabu/boost-javaabu-guidelines)): snake_case variables and properties, camelCase methods, early returns, `#[Test]` with snake_case names. Pint enforces formatting.
- Nothing may assume the `web` guard, `App\Models\User` or global route names. Resolve the type with `UserTypes::fromRequest()`, load users through `MobileVerification::userFor()` (the type's guard provider), and test both test types, including that one type's number doesn't log in another.
- Codes are security-sensitive: keep them hashed, single-use, expiring, counted and locked, and test the failure cases (wrong, expired, superseded, locked, rate limited).
- Inertia, Sanctum and Passport are optional: feature-detect them, never `use` their classes in code that always runs, keep CI's "Without the optional packages" job green.
- Every page exists in Blade and React with the same props. Logical utilities only (a test rejects `ml-`, `text-left`, …); numbers and codes are `dir="ltr"`.
- Every user-facing string goes through `__()` with an English key, a Dhivehi line in `lang/dv.json` and an English line in `lang/en.json` (`tests/Unit/TranslationsTest.php` checks). React strings go in `Support\Strings`; regenerate `tests/js/strings.dv.json` after changing them:

  ```bash
  php -r 'require "vendor/autoload.php"; $dv = json_decode(file_get_contents("lang/dv.json"), true); $auth = json_decode(file_get_contents("vendor/javaabu/auth/lang/dv.json"), true); $out = []; foreach (Javaabu\MobileVerification\Support\Strings::ALL as $k => $en) { $out[$k] = $dv[$en] ?? $auth[$en] ?? $en; } foreach (["account" => "Account", "profile" => "Profile", "security" => "Security", "log_out" => "Log out"] as $k => $en) { $out[$k] = $auth[$en] ?? $en; } ksort($out); file_put_contents("tests/js/strings.dv.json", json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");'
  ```

- List uncertain Dhivehi in `docs/advanced-usage/translations.md`. Use Thaana in tests of user-facing behaviour.
- Never depend on `javaabu/helpers` or `javaabu/settings` ^1 (CI checks the production tree).
- Every change needs tests, docs (linked from `llms.txt`), the Boost skill if it changes how the package is used, and a line in `CHANGELOG.md`.
- Work on the `next-gen` branch; don't commit to `main`.

## Commands

```bash
composer install
composer lint      # vendor/bin/pint --test
composer format    # vendor/bin/pint
composer test      # vendor/bin/phpunit
pnpm install       # the React pages' dev tooling (after composer install)
pnpm check         # typecheck and Vitest
```
