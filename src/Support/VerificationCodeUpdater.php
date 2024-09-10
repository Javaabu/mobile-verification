<?php

namespace Javaabu\MobileVerification\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class VerificationCodeUpdater
{
    public function __construct(
        public VerificationCodeGenerator $verificationCodeGenerator
    )
    {
    }
    public function handle(Model $mobile_number): string
    {
        $verification_code = $this->verificationCodeGenerator->handle();

        $mobile_number->attempts = 0;
        $mobile_number->verification_code = $verification_code;
        $mobile_number->verification_code_created_at = Carbon::now();
        $mobile_number->verification_code_id = Str::uuid();
        $mobile_number->save();

        return $verification_code;
    }
}
