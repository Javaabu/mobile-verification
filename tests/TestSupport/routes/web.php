<?php

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\LoginController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\RegisterController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\SendTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\MobileNumberUpdateController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\MobileNumberUpdateTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\ValidateMobileNumbersController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyMobileNumberAvailabilityController;

Route::get('/', function () {
    return "Testing Javaabu Mobile Verification";
});

Route::post('/validate', [ValidateMobileNumbersController::class, 'validate'])->name('validate');
Route::post('/mobile-number-otp', [SendTokenController::class, 'mobileNumberOtp'])->name('mobile-number-otp');
Route::post('/verify', [VerifyTokenController::class, 'verify'])->name('verify');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::get('/protected', function () {
    return "Protected Route";
})->name('protected')->middleware('auth:web');

Route::post('/request-top', [MobileNumberUpdateTokenController::class, 'requestOtp'])
     ->name('request-number-change-otp')
     ->middleware('auth:web');

Route::post('/update-mobile-number', [MobileNumberUpdateController::class, 'update'])
    ->name('update-mobile-number')
    ->middleware('auth:web');

