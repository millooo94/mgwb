<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\AccountRecoveryVerifyRequest;
use App\Services\Auth\AccountRecoveryVerifyService;
use App\Support\ApiResponse;

class AccountRecoveryVerifyController extends Controller
{
    public function __invoke(
        AccountRecoveryVerifyRequest $request,
        AccountRecoveryVerifyService $accountRecoveryVerifyService
    ) {
        $result = $accountRecoveryVerifyService->verify($request->validated());

        return ApiResponse::success(
            data: $result,
            message: 'Identità verificata con successo.'
        );
    }
}
