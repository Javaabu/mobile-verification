<?php

namespace Javaabu\MobileVerification\Http\Controllers;

use Illuminate\View\View;
use Javaabu\MobileVerification\Traits\CanRegisterUsingToken;
use Javaabu\MobileVerification\Traits\HasFormView;

abstract class RegistrationController
{
    use CanRegisterUsingToken;
    use HasFormView;

    public function showRegistrationForm(): View
    {
        return $this->getFormView();
    }
}
