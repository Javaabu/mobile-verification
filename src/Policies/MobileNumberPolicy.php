<?php

namespace Javaabu\MobileVerification\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Auth\UserType;

/**
 * What admins may do to other users' mobile numbers. The abilities take the managed user type
 * and the account: `Gate::allows('mobile-verification.update', [$type, $account])`.
 *
 * | Ability                       | Permission                       |
 * | ----------------------------- | -------------------------------- |
 * | mobile-verification.update    | update_{type}_mobile_numbers     |
 * | mobile-verification.unlock    | unlock_{type}_mobile_numbers     |
 * | mobile-verification.remove    | remove_{type}_mobile_numbers     |
 *
 * Replace it with `mobile-verification.policy`, or redefine an ability with Gate::define().
 */
class MobileNumberPolicy
{
    /** ability => action, the permission being `{action}_{type}_mobile_numbers` */
    public const array ABILITIES = [
        'mobile-verification.update' => 'update',
        'mobile-verification.unlock' => 'unlock',
        'mobile-verification.remove' => 'remove',
    ];

    /** English descriptions of the permissions, used as translation keys */
    public const array DESCRIPTIONS = [
        'update' => 'Change the mobile numbers of :resource',
        'unlock' => 'Unlock the locked mobile numbers of :resource',
        'remove' => 'Remove the mobile numbers of :resource',
    ];

    public function update(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'update', $type);
    }

    public function unlock(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'unlock', $type);
    }

    public function remove(Model $user, UserType $type, ?Model $account = null): bool
    {
        return $this->allows($user, 'remove', $type);
    }

    /**
     * The permission group: the plural of the type (`users`, `admins`), as javaabu/auth uses
     */
    public static function group(UserType $type): string
    {
        return str($type->name())->plural()->snake()->toString();
    }

    public static function permission(string $action, UserType $type): string
    {
        return "{$action}_" . str($type->name())->snake()->toString() . '_mobile_numbers';
    }

    protected function allows(Model $user, string $action, UserType $type): bool
    {
        return $user->can(static::permission($action, $type));
    }
}
