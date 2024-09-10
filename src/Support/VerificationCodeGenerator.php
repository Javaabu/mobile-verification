<?php

namespace Javaabu\MobileVerification\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class VerificationCodeGenerator
{
    public function handle(): string
    {
        return str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
    }
}
