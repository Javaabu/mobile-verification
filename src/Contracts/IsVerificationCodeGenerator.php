<?php


namespace Javaabu\MobileVerification\Contracts;


interface IsVerificationCodeGenerator
{
    public function handle(): string;
}
