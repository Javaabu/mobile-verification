<?php

namespace Javaabu\MobileVerification\Traits;

use Javaabu\Helpers\Exceptions\InvalidOperationException;
use Javaabu\MobileVerification\Contracts\HasUserTypeContract;

/* @var HasUserTypeContract $this */
trait HasUserType
{
    public function getUserType(): string
    {
        return (new ($this->getUserClass()))->getMorphClass();
    }
}
