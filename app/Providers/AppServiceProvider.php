<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        /*
        |--------------------------------------------------------------------------
        | RATE LIMIT LOGIN
        |--------------------------------------------------------------------------
        */

        RateLimiter::for('login', function (Request $request) {

            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($email . $request->ip());
        });


        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN BYPASS
        |--------------------------------------------------------------------------
        */

        Gate::before(function ($user, $ability) {
            return $user->hasRole('amministratore') ? true : null;
        });


        /*
        |--------------------------------------------------------------------------
        | RESET PASSWORD URL (FRONTEND)
        |--------------------------------------------------------------------------
        */

        ResetPassword::createUrlUsing(function ($user, string $token) {

            $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');

            return "{$frontendUrl}/reset-password/{$token}?email=" . urlencode($user->email);
        });
    }
}
