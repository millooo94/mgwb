<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return ApiResponse::error(
                'Utente non autenticato.',
                401
            );
        }

        if (! $user->hasVerifiedEmail()) {
            return ApiResponse::error(
                'Devi verificare la tua email.',
                403
            );
        }

        return $next($request);
    }
}
