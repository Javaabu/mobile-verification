<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\Contracts\Auth\StatefulGuard;
use Javaabu\MobileVerification\Contracts\HasGuardContract;

/* @var HasGuardContract $this */
trait UsesGuard
{
    public function guard(): StatefulGuard
    {
        return auth()->guard($this->getGuardName());
    }
}
