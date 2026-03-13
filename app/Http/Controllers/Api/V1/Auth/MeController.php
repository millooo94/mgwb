<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthUserPayloadService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request, AuthUserPayloadService $authUserPayloadService)
    {
        $payload = $authUserPayloadService->build($request->user());

        return ApiResponse::success($payload);
    }
}