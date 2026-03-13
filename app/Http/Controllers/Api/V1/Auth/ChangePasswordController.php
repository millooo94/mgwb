<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Services\Auth\ChangePasswordService;
use App\Support\ApiResponse;

class ChangePasswordController extends Controller
{
    public function __invoke(
        ChangePasswordRequest $request,
        ChangePasswordService $changePasswordService
    ) {
        $changePasswordService->changePassword(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            null,
            'Password aggiornata con successo.'
        );
    }
}
