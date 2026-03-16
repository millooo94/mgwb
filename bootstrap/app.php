<?php

use App\Exceptions\AccountRecoveryException;
use App\Exceptions\ChangeEmailException;
use App\Exceptions\ChangePasswordException;
use App\Exceptions\ChangePhoneException;
use App\Exceptions\ForgotPasswordException;
use App\Exceptions\LoginAppleException;
use App\Exceptions\LoginException;
use App\Exceptions\LoginFacebookException;
use App\Exceptions\LoginGoogleException;
use App\Exceptions\PhoneVerificationException;
use App\Exceptions\RegisterException;
use App\Exceptions\ResendVerificationEmailException;
use App\Exceptions\ResetPasswordException;
use App\Exceptions\SetPasswordException;
use App\Exceptions\UpdateProfileException;
use App\Exceptions\VerifyEmailException;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => \App\Http\Middleware\EnsureActiveUser::class,
            'backoffice' => \App\Http\Middleware\EnsureBackofficeAccess::class,
            'customer' => \App\Http\Middleware\EnsureCustomerAccess::class,
            'verified' => \App\Http\Middleware\EnsureEmailVerified::class,
            'active' => \App\Http\Middleware\EnsureActiveUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: 'Utente non autenticato.',
                    status: 401
                );
            }

            return null;
        });

        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: 'Dati non validi.',
                    status: 422,
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (LoginException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (RegisterException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ChangePasswordException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ForgotPasswordException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ResetPasswordException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (VerifyEmailException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ResendVerificationEmailException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (PhoneVerificationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (AccountRecoveryException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (LoginGoogleException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (LoginFacebookException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (LoginAppleException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (UpdateProfileException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ChangeEmailException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (SetPasswordException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });

        $exceptions->render(function (ChangePhoneException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: $e->getMessage(),
                    status: $e->status(),
                    errors: $e->errors()
                );
            }

            return null;
        });
    })
    ->create();
