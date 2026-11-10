---
title: Registration
sidebar_position: 3
---

User types with `MobileLoginMethod` and javaabu/auth's `registration` feature also let visitors sign up with a mobile number. The mobile login page links to it ("Don't have an account? Sign up").

1. **The number.** `GET register/mobile`, then `POST register/mobile` checks that no user of this type has the number and sends a code.
2. **The code.** `GET register/mobile/verify`, then `POST register/mobile/verify` checks it. The verified number is remembered in the session for `verification_code_validity` minutes.
3. **The details.** `GET register/mobile/details` shows the registration form. `POST register/mobile/details` validates it, creates the user, gives them the number, fires Laravel's `Registered` event, logs them in and answers with javaabu/auth's `RegisterResponse`.

If someone else takes the number before the form is sent, the user goes back to the first step with "This mobile number is already registered."

Turn mobile registration off for every type with `'registration' => false` in `config/mobile-verification.php`, or for one type with javaabu/auth's feature: `'features' => ['registration' => false]`.

# Routes

| Method | URL | Name | |
| --- | --- | --- | --- |
| GET, POST | `register/mobile` | `register.mobile`, `register.mobile.store` | The number |
| GET, POST | `register/mobile/verify` | `register.mobile.verify`, `register.mobile.verify.store` | The code |
| POST | `register/mobile/resend` | `register.mobile.resend` | Another code |
| GET, POST | `register/mobile/details` | `register.mobile.details`, `register.mobile.details.store` | The form |

# The form

The details form is a [javaabu/stencil](https://github.com/Javaabu/stencil) form, so one definition renders in Blade and React and validates on the server. The default, `Javaabu\MobileVerification\Stencil\MobileRegistrationForm`, asks for a name (required, typed in Thaana on Dhivehi pages) and an email address (optional, unique in the user table).

Ask for more by extending it and setting it in config:

```php
namespace App\Stencil;

use Javaabu\MobileVerification\Stencil\MobileRegistrationForm as BaseForm;
use Javaabu\Stencil\Fields\Select;
use Javaabu\Stencil\Fields\TextInput;

class MobileRegistrationForm extends BaseForm
{
    protected function definition(): array
    {
        return [
            ...parent::definition(),
            TextInput::make('id_card')->label(__('ID card number'))->required()->rules(['regex:/^A\d{6}$/']),
            Select::make('island_id')->label(__('Island'))->relationship('island', 'name')->required(),
        ];
    }
}
```

```php
// config/mobile-verification.php
'registration_form' => App\Stencil\MobileRegistrationForm::class,
```

The form gets the user type's model class as its model (for `unique()` and relationships), and the type's name in its context: `$this->context('user_type')`.

# Creating the user

The form's validated values go to `Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers`. The default saves them on a new user of the type with `forceFill()` (they're limited to the form's fields), lowercasing the email. Bind your own to set more:

```php
namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers;
use Javaabu\MobileVerification\Models\MobileNumber;

class CreateUserFromMobileNumber implements CreatesUsersFromMobileNumbers
{
    public function create(UserType $type, array $input, MobileNumber $mobile_number): Model
    {
        $user = $type->newModel();

        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'] ?? null,
            'id_card' => mb_strtoupper($input['id_card']),
        ]);

        $user->approve();
        $user->save();

        return $user;
    }
}
```

```php
// AppServiceProvider::register()
$this->app->bind(
    \Javaabu\MobileVerification\Contracts\CreatesUsersFromMobileNumbers::class,
    \App\Actions\CreateUserFromMobileNumber::class,
);
```

Don't attach the number or log the user in: the package does both after `create()` returns.

New users get javaabu/auth's default account status, `pending`, as with its own registration, and `auth.type` sends pending users to the "account not active" page. Approve them in your action (as above) if they may use the app straight away.
