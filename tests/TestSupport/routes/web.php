<?php

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyMobileNumberAvailabilityController;

Route::get('/verify', [VerifyMobileNumberAvailabilityController::class, 'verify'])->name('verify');
