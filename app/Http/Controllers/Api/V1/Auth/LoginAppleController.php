<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginAppleRequest;
use App\Services\Auth\LoginAppleService;
use App\Support\ApiResponse;

class LoginAppleController extends Controller
{
    public function __invoke(
        LoginAppleRequest $request,
        LoginAppleService $loginAppleService
    ) {
        $result = $loginAppleService->login($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Login effettuato con successo.'
        );
    }
}
