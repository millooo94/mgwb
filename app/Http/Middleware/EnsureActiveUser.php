<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveUser
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

        if (! in_array((int) $user->stato, [1, 3], true)) {
            return ApiResponse::error(
                'Account disattivato.',
                403
            );
        }

        return $next($request);
    }
}
