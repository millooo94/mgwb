<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Services\Auth\ResetPasswordService;
use App\Support\ApiResponse;

class ResetPasswordController extends Controller
{
    public function __invoke(
        ResetPasswordRequest $request,
        ResetPasswordService $resetPasswordService
    ) {
        $resetPasswordService->resetPassword(
            $request->validated()
        );

        return ApiResponse::success(
            null,
            'Password aggiornata con successo.'
        );
    }
}