<?php

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\RegisterController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\ValidateMobileNumbersController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyMobileNumberAvailabilityController;

Route::get('/', function () {
    return "Testing Javaabu Mobile Verification";
});

Route::post('/validate', [ValidateMobileNumbersController::class, 'validate'])->name('validate');
Route::get('/verify', [VerifyMobileNumberAvailabilityController::class, 'verify'])->name('verify');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
