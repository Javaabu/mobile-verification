<?php

namespace Javaabu\MobileVerification\Traits;

use Illuminate\View\View;
use Javaabu\Helpers\Exceptions\InvalidOperationException;

trait HasFormView
{
    public function getFormView(): View
    {
        if (property_exists($this, 'form_view')) {
            return view($this->form_view);
        }

        throw new InvalidOperationException('The form view is not defined in the controller.');
//        return 'mobile-verification::otp.form';
    }
}
