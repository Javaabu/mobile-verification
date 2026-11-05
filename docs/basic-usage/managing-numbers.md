---
title: Managing numbers
sidebar_position: 7
---

Admins help users who lost their number or locked it: set a new number without a code, unlock a locked number, or remove it. `Route::mobileNumberActions()` adds these for one user type. Put them behind the admins' middleware:

```php
Route::mobileNumberActions('user', [
    'prefix' => 'admin/users',
    'middleware' => ['web', 'auth.type:admin'],
]);
```

| Method | URL | Name | Ability | Permission |
| --- | --- | --- | --- | --- |
| PUT | `{prefix}/{account}/mobile-number` | `users.mobile-number.update` | `mobile-verification.update` | `update_user_mobile_numbers` |
| POST | `{prefix}/{account}/mobile-number/unlock` | `users.mobile-number.unlock` | `mobile-verification.unlock` | `unlock_user_mobile_numbers` |
| DELETE | `{prefix}/{account}/mobile-number` | `users.mobile-number.destroy` | `mobile-verification.remove` | `remove_user_mobile_numbers` |

`{account}` is the user's route key. `update` takes `number` and `country_code`; the number must not belong to another user of the type. The name prefix defaults to the plural of the type (`users.mobile-number`); set `name` to change it. Each action answers JSON (`{"message": …}`) or redirects back with a status. Changes fire `MobileNumberUpdated` with the admin as the causer.

```blade
<x-forms::form method="PUT" :action="route('users.mobile-number.update', $user)">
    <input type="hidden" name="country_code" value="960">
    <x-forms::tel name="number" :label="__('Mobile number')" :default="$user->mobile_number" dir="ltr" required />
    <x-forms::submit>{{ __('Save') }}</x-forms::submit>
</x-forms::form>

@can('mobile-verification.unlock', [\Javaabu\Auth\UserTypes::get('user'), $user])
    <x-forms::form :action="route('users.mobile-number.unlock', $user)">
        <x-forms::submit>{{ __('Unlock') }}</x-forms::submit>
    </x-forms::form>
@endcan
```

# Permissions

The package registers three permissions for every user type through javaabu/permissions, in the type's plural group (`users`, `admins`), next to javaabu/auth's: `update_{type}_mobile_numbers`, `unlock_{type}_mobile_numbers`, `remove_{type}_mobile_numbers`. Run `php artisan permissions:sync` after installing. Limit them to some types, or turn them off:

```php
// config/mobile-verification.php
'managed_user_types' => ['user'],
'register_permissions' => false,
```

# The policy

The abilities are defined on the gate and point at `Javaabu\MobileVerification\Policies\MobileNumberPolicy`, which checks the permission of the managed type. They take the managed type and the account:

```php
use Illuminate\Support\Facades\Gate;
use Javaabu\Auth\UserTypes;

Gate::allows('mobile-verification.update', [UserTypes::get('user'), $user]);
```

Replace it in config with a class extending the package's:

```php
'policy' => App\Policies\MobileNumberPolicy::class,
```

```php
namespace App\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;
use Javaabu\MobileVerification\Policies\MobileNumberPolicy as BasePolicy;

class MobileNumberPolicy extends BasePolicy
{
    public function remove(Model $user, UserType $type, ?Model $account = null): bool
    {
        // Admins can't remove their own number from here
        return parent::remove($user, $type, $account) && ! $account?->is($user);
    }
}
```

Or redefine one ability in a service provider: `Gate::define('mobile-verification.unlock', fn ($user, $type, $account) => $user->isSuperAdmin())`. The package doesn't replace abilities that are already defined.
