<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClienteAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return ApiResponse::error('Utente non autenticato.', 401);
        }

        if (! $user->hasRole('cliente')) {
            return ApiResponse::error('Accesso negato all’area cliente.', 403);
        }

        if ((int) $user->stato !== 1) {
            return ApiResponse::error('Account cliente disattivato.', 403);
        }

        return $next($request);
    }
}
