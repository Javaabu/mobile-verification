<?php

use Illuminate\Support\Facades\Route;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\LoginController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\RegisterController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\SendTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\VerifyTokenController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\MobileNumberUpdateController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\ValidateMobileNumbersController;
use Javaabu\MobileVerification\Tests\TestSupport\Controllers\MobileNumberUpdateTokenController;

Route::get('/', function () {
    return "Testing Javaabu Mobile Verification";
});

Route::get('/login', function () {
    return "Login";
})->name('login');

Route::group([
    'middleware' => 'web',
    'prefix' => '/mobile-verification',
], function () {
    Route::get('/login', [LoginController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.login.create');
    Route::post('/login', [LoginController::class, 'requestVerificationCode'])->name('mobile-verifications.login.store');
    Route::match(['PATCH', 'PUT'], '/login', [LoginController::class, 'verifyVerificationCode'])->name('mobile-verifications.login.update');


    Route::get('/register', [RegisterController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.register.create');
    Route::post('/register', [RegisterController::class, 'requestVerificationCode'])->name('mobile-verifications.register.store');
    Route::match(['PATCH', 'PUT'],'/register', [RegisterController::class, 'register'])->name('mobile-verifications.register.update');

    Route::get('/update', [MobileNumberUpdateController::class, 'showVerificationCodeRequestForm'])->name('mobile-verifications.update.create');
    Route::post('/update', [MobileNumberUpdateController::class, 'requestVerificationCode'])->name('mobile-verifications.update.store');
    Route::match(['PATCH', 'PUT'],'/update', [MobileNumberUpdateController::class, 'verifyVerificationCode'])->name('mobile-verifications.update.update');


    Route::get('/updated', function (){
        return "Mobile number updated";
    })->name('mobile-verifications.updated');

    Route::get('/protected', function () {
        return "Protected route";
    })->middleware(['auth:web', 'mobile-verified:web']);
});


Route::group([
    'prefix' => 'api'
], function () {
    Route::get('/protected', function () {
        return "Protected route";
    })->middleware(['auth:web', 'mobile-verified:web']);
});
