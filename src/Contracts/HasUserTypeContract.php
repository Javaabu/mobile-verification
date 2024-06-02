<?php

namespace Javaabu\MobileVerification\Contracts;

use Illuminate\Http\Request;

interface HasUserTypeContract
{
    public function getUserClass(Request $request): string;

    public function getUserType(Request $request): string;
}
