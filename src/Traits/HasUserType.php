<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\Helpers\Exceptions\InvalidOperationException;

trait HasUserType
{
    public function getUserType(): string
    {
        if (method_exists($this, 'getUserClass')) {
            return (new ($this->getUserClass()))->getMorphClass();
        }

        throw new InvalidOperationException('The user class property is not defined in the class');
    }
}
