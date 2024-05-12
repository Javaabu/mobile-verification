<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\Helpers\Exceptions\InvalidOperationException;

trait HasUserGuard
{
    public function getUserGuard(): string
    {
        if (property_exists($this, 'user_guard')) {
            return $this->user_guard;
        }

        throw new InvalidOperationException('The user guard property is not defined in the class');
    }
}
