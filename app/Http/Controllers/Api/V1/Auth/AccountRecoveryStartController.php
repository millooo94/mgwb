<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\AccountRecoveryStartRequest;
use App\Services\Auth\AccountRecoveryStartService;
use App\Support\ApiResponse;

class AccountRecoveryStartController extends Controller
{
    public function __invoke(
        AccountRecoveryStartRequest $request,
        AccountRecoveryStartService $accountRecoveryStartService
    ) {
        $result = $accountRecoveryStartService->start($request->validated());

        return ApiResponse::success(
            data: [
                'sent' => $result['sent'],
            ],
            message: $result['message']
        );
    }
}
