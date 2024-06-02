<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Http\Request;
use Javaabu\MobileVerification\Contracts\HasUserTypeContract;

/* @var HasUserTypeContract $this */
trait HasUserType
{
    public function getUserType(Request $request): string
    {
        return (new ($this->getUserClass($request)))->getMorphClass();
    }
}
