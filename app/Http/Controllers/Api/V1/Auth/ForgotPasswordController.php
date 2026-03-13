<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Services\Auth\ForgotPasswordService;
use App\Support\ApiResponse;

class ForgotPasswordController extends Controller
{
    public function __invoke(
        ForgotPasswordRequest $request,
        ForgotPasswordService $forgotPasswordService
    ) {
        $forgotPasswordService->sendResetLink(
            $request->validated()
        );

        return ApiResponse::success(
            null,
            'Email di reset inviata con successo.'
        );
    }
}
