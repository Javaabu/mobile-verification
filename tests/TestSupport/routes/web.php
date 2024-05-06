<?php

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\ValidateMobileNumbersController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyMobileNumberAvailabilityController;

Route::post('/validate', [ValidateMobileNumbersController::class, 'validate'])->name('validate');
Route::get('/verify', [VerifyMobileNumberAvailabilityController::class, 'verify'])->name('verify');
