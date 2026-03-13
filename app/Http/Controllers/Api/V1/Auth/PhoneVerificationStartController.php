<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\PhoneVerificationStartRequest;
use App\Services\Auth\PhoneVerificationStartService;
use App\Support\ApiResponse;

class PhoneVerificationStartController extends Controller
{
    public function __invoke(
        PhoneVerificationStartRequest $request,
        PhoneVerificationStartService $phoneVerificationStartService
    ) {
        $result = $phoneVerificationStartService->start(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            data: [
                'already_verified' => $result['already_verified'],
                'sent' => $result['sent'],
            ],
            message: $result['message']
        );
    }
}
