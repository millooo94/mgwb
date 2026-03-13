<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginFacebookRequest;
use App\Services\Auth\LoginFacebookService;
use App\Support\ApiResponse;

class LoginFacebookController extends Controller
{
    public function __invoke(
        LoginFacebookRequest $request,
        LoginFacebookService $loginFacebookService
    ) {
        $result = $loginFacebookService->login($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Login effettuato con successo.'
        );
    }
}
