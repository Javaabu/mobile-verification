<?php

namespace Javaabu\MobileVerification\Stencil;

use Javaabu\Stencil\Fields\TextInput;
use Javaabu\Stencil\Form;

/**
 * The details a user gives after verifying their number on the mobile registration.
 * Replace it with `mobile-verification.registration_form` to ask for more; the fields it
 * validates are what Contracts\CreatesUsersFromMobileNumbers gets.
 */
class MobileRegistrationForm extends Form
{
    protected function setUp(): void
    {
        $this->id('mobile-registration-form')
            ->submit(__('Create account'));
    }

    protected function definition(): array
    {
        return [
            TextInput::make('name')
                ->label(__('Name'))
                ->required()
                ->maxLength(255)
                ->autocomplete('name')
                ->thaana(fn () => app()->getLocale() === 'dv'),

            TextInput::make('email')
                ->label(__('Email address'))
                ->email()
                ->maxLength(255)
                ->unique()
                ->autocomplete('email')
                ->helperText(__('Optional')),
        ];
    }
}
