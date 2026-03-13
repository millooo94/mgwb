<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\PhoneVerificationConfirmRequest;
use App\Services\Auth\PhoneVerificationConfirmService;
use App\Support\ApiResponse;

class PhoneVerificationConfirmController extends Controller
{
    public function __invoke(
        PhoneVerificationConfirmRequest $request,
        PhoneVerificationConfirmService $phoneVerificationConfirmService
    ) {
        $phoneVerificationConfirmService->confirm(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            data: null,
            message: 'Numero di telefono verificato con successo.'
        );
    }
}
