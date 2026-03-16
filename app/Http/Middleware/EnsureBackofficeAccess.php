<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBackofficeAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return ApiResponse::error('User is not authenticated.', 401);
        }

        if (! $user->hasAnyRole(['superadmin', 'admin', 'staff'])) {
            return ApiResponse::error('Access denied to the backoffice area.', 403);
        }

        if (! in_array((int) $user->stato, [1, 3], true)) {
            return ApiResponse::error('User is not allowed to access the backoffice.', 403);
        }

        return $next($request);
    }
}
