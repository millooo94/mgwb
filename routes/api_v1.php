<?php

use App\Http\Controllers\Api\V1\Auth\AccountRecoveryStartController;
use App\Http\Controllers\Api\V1\Auth\AccountRecoveryVerifyController;
use App\Http\Controllers\Api\V1\Auth\ChangeEmailConfirmController;
use App\Http\Controllers\Api\V1\Auth\ChangeEmailStartController;
use App\Http\Controllers\Api\V1\Auth\ChangePasswordController;
use App\Http\Controllers\Api\V1\Auth\ChangePhoneConfirmController;
use App\Http\Controllers\Api\V1\Auth\ChangePhoneStartController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginAppleController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LoginFacebookController;
use App\Http\Controllers\Api\V1\Auth\LoginGoogleController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationConfirmController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationStartController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResendVerificationEmailController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\SetPasswordController;
use App\Http\Controllers\Api\V1\Auth\UpdateProfileController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', RegisterController::class)
            ->middleware('throttle:5,1');

        Route::post('/login', LoginController::class)
            ->middleware('throttle:login');

        Route::post('/login-google', LoginGoogleController::class)
            ->middleware('throttle:10,1');

        Route::post('/login-facebook', LoginFacebookController::class)
            ->middleware('throttle:10,1');

        Route::post('/login-apple', LoginAppleController::class)
            ->middleware('throttle:10,1');

        Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
            ->middleware('throttle:6,1')
            ->name('verification.verify');

        Route::get('/reset-password/{token}', function (string $token) {
            return response()->json([
                'token' => $token,
                'email' => request()->query('email'),
            ]);
        })->name('password.reset');

        Route::post('/forgot-password', ForgotPasswordController::class)
            ->middleware('throttle:5,1');

        Route::post('/reset-password', ResetPasswordController::class)
            ->middleware('throttle:5,1');

        Route::post('/account-recovery/start', AccountRecoveryStartController::class)
            ->middleware('throttle:3,10');

        Route::post('/account-recovery/verify', AccountRecoveryVerifyController::class)
            ->middleware('throttle:5,10');
    });

    Route::prefix('auth')
        ->middleware(['auth:sanctum'])
        ->group(function () {
            Route::get('/me', MeController::class);

            Route::post('/logout', LogoutController::class);

            Route::post('/email/verification-notification', ResendVerificationEmailController::class)
                ->middleware('throttle:6,1');

            Route::post('/phone-verification/start', PhoneVerificationStartController::class)
                ->middleware('throttle:3,10');

            Route::post('/phone-verification/confirm', PhoneVerificationConfirmController::class)
                ->middleware('throttle:5,10');
        });

    Route::prefix('auth')
        ->middleware(['auth:sanctum', 'active'])
        ->group(function () {
            Route::post('/change-password', ChangePasswordController::class);
            Route::put('/profile', UpdateProfileController::class);

            Route::post('/set-password', SetPasswordController::class);

            Route::post('/email-change/start', ChangeEmailStartController::class);
            Route::post('/email-change/confirm', ChangeEmailConfirmController::class);

            Route::post('/phone-change/start', ChangePhoneStartController::class);
            Route::post('/phone-change/confirm', ChangePhoneConfirmController::class);
        });

    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'active', 'admin'])
        ->group(function () {
            Route::get('/ping', fn() => ApiResponse::success(['pong' => true], 'admin ok'));
        });

    Route::prefix('cliente')
        ->middleware(['auth:sanctum', 'active', 'verified', 'cliente'])
        ->group(function () {
            Route::get('/ping', fn() => ApiResponse::success(['pong' => true], 'cliente ok'));
        });
});
