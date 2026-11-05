<?php

namespace Javaabu\MobileVerification\Tests\Fixtures;

use Javaabu\MobileVerification\Stencil\MobileRegistrationForm;
use Javaabu\Stencil\Fields\TextInput;

class RegistrationFormWithIdCard extends MobileRegistrationForm
{
    protected function definition(): array
    {
        return [
            TextInput::make('name')->label(__('Name'))->required()->maxLength(255),
            TextInput::make('id_card')->label(__('ID card'))->required()->rules(['regex:/^A\d{6}$/'])->dehydrated(),
        ];
    }
}
