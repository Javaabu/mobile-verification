<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;

interface HasGuardContract
{
    public function guard(): StatefulGuard;

    public function getGuardName(): string;

}
