<?php

namespace Javaabu\MobileVerification\Contracts;

interface HasUserTypeContract
{
    public function getUserClass(): string;

    public function getUserType(): string;
}
