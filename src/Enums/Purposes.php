<?php

namespace Javaabu\MobileVerification\Enums;

/**
 * What a verification code is for
 */
enum Purposes: string
{
    case LOGIN = 'login';
    case REGISTER = 'register';
    case UPDATE = 'update';

    /**
     * Whether the number must already belong to a user of the type
     */
    public function needsOwner(): bool
    {
        return $this === self::LOGIN;
    }

    /**
     * Whether the number must not belong to a user of the type yet
     */
    public function needsNoOwner(): bool
    {
        return $this !== self::LOGIN;
    }
}
