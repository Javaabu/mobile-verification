<?php

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\LoginController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\MobileNumberUpdateController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\MobileNumberUpdateTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\RegisterController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\SendTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\ValidateMobileNumbersController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyTokenController;

Route::get('/', function () {
    return "Testing Javaabu Mobile Verification";
});

Route::group([
    'middleware' => 'web',
    'prefix' => '/mobile-verification',
], function () {
    Route::get('/login', [LoginController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.login.create');
    Route::post('/login', [LoginController::class, 'requestVerificationCode'])->name('mobile-verifications.login.store');


    Route::get('/register', [RegisterController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.register.create');
    Route::post('/register', [RegisterController::class, 'register'])->name('mobile-verifications.register.store');

    Route::get('/protected', function () {
        return "Protected route";
    })->middleware('mobile-verified:web');
});

//Route::post('/validate', [ValidateMobileNumbersController::class, 'validate'])->name('validate');
//Route::post('/mobile-number-otp', [SendTokenController::class, 'mobileNumberOtp'])->name('mobile-number-otp');
//Route::post('/verify', [VerifyTokenController::class, 'verify'])->name('verify');
//Route::post('/register', [RegisterController::class, 'register'])->name('register');
//Route::post('/login', [LoginController::class, 'login'])->name('login');
//
//Route::get('/protected', function () {
//    return "Protected Route";
//})->name('protected')->middleware('auth:web');
//
//Route::post('/request-top', [MobileNumberUpdateTokenController::class, 'mobileNumberOtp'])
//     ->name('request-number-change-otp')
//     ->middleware('auth:web');
//
//Route::post('/update-mobile-number', [MobileNumberUpdateController::class, 'update'])
//    ->name('update-mobile-number')
//    ->middleware('auth:web');
//
//Route::get('/api-protected', function () {
//    return "Api Protected Route";
//})->name('api-protected')->middleware('auth:sanctum');
