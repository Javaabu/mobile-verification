<?php

namespace Javaabu\MobileVerification\Tests\Fixtures;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Javaabu\MobileVerification\Http\Controllers\MobileLoginController;

class CustomMobileLoginController extends MobileLoginController
{
    public function create(Request $request): View|Responsable
    {
        return view('custom-mobile-login', ['type' => $this->userType($request)->name()]);
    }
}
