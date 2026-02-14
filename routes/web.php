<?php

use Illuminate\Support\Facades\Route;
use Skywalker\Otp\Http\Controllers\OtpAuthController;

Route::group(['middleware' => ['web']], function () {
    // API Routes
    Route::post('/otp/send', [OtpAuthController::class, 'sendOtp'])->name('otp.send');
    Route::post('/otp/verify', [OtpAuthController::class, 'verifyOtp'])->name('otp.verify');

    // Session/Web Routes (Protected by Auth, but excluded from otp.verified middleware check in logic)
    Route::middleware(['auth'])->group(function () {
        Route::get('/otp/verify', [OtpAuthController::class, 'showVerifyForm'])->name('otp.verify.view');
        Route::post('/otp/verify-submit', [OtpAuthController::class, 'verifyOtpSubmit'])->name('otp.verify.submit');
        Route::post('/otp/resend', [OtpAuthController::class, 'resendOtp'])->name('otp.resend');
    });

    // Magic Link (Public, Signed)
    Route::get('/magic-login', [OtpAuthController::class, 'loginMagic'])->name('passwordless.magic-login');
});

