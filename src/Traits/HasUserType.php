<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\Helpers\Exceptions\InvalidOperationException;

trait HasUserType
{
    public function getUserType(): string
    {
        if (property_exists($this, 'user_class')) {
            return (new $this->user_class())->getMorphClass();
        }

        throw new InvalidOperationException('The user class property is not defined in the class');
    }
}
