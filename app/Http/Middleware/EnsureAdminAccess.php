<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return ApiResponse::error('Utente non autenticato.', 401);
        }

        if (! $user->hasAnyRole(['amministratore', 'collaboratore'])) {
            return ApiResponse::error('Accesso negato all’area admin.', 403);
        }

        if (! in_array((int) $user->stato, [1, 3], true)) {
            return ApiResponse::error('Utente non autorizzato al backoffice.', 403);
        }

        return $next($request);
    }
}
