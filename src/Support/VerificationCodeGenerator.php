<?php

namespace Javaabu\MobileVerification\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Javaabu\MobileVerification\Contracts\IsVerificationCodeGenerator;

class VerificationCodeGenerator implements IsVerificationCodeGenerator
{
    public function handle(): string
    {
        return str_pad(rand(0, 999999), 6, 0, STR_PAD_LEFT);
    }
}
