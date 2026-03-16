<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return ApiResponse::error('User is not authenticated.', 401);
        }

        if (! $user->hasRole('customer')) {
            return ApiResponse::error('Access denied to the customer area.', 403);
        }

        if ((int) $user->stato !== 1) {
            return ApiResponse::error('Customer account is not active.', 403);
        }

        return $next($request);
    }
}
