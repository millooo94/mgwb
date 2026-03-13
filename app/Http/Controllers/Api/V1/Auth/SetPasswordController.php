<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SetPasswordRequest;
use App\Services\Auth\SetPasswordService;
use App\Support\ApiResponse;

class SetPasswordController extends Controller
{
    public function __invoke(
        SetPasswordRequest $request,
        SetPasswordService $setPasswordService
    ) {
        $setPasswordService->set(
            user: $request->user(),
            data: $request->validated()
        );

        return ApiResponse::success(
            data: [
                'password_set' => true,
            ],
            message: 'Password impostata con successo.'
        );
    }
}
