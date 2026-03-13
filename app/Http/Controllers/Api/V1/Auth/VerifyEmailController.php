<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\VerifyEmailRequest;
use App\Services\Auth\VerifyEmailService;
use App\Support\ApiResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(
        VerifyEmailRequest $request,
        VerifyEmailService $verifyEmailService
    ) {
        $message = $verifyEmailService->verify($request);

        return ApiResponse::success(
            data: null,
            message: $message,
            status: 200
        );
    }
}