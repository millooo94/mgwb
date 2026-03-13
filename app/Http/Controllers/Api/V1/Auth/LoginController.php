<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use App\Support\ApiResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginService $loginService)
    {
        $result = $loginService->login($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Login effettuato con successo.'
        );
    }
}