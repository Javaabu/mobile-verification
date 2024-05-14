<?php

namespace Javaabu\MobileVerification\Contracts;


interface IsANumberFormatValidator
{
    public function handle(string $country_code, string $number): bool;
}
