<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginGoogleRequest;
use App\Services\Auth\LoginGoogleService;
use App\Support\ApiResponse;

class LoginGoogleController extends Controller
{
    public function __invoke(
        LoginGoogleRequest $request,
        LoginGoogleService $loginGoogleService
    ) {
        $result = $loginGoogleService->login($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Login effettuato con successo.'
        );
    }
}
