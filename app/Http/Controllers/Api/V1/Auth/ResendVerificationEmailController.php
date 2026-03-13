<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\ResendVerificationEmailService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ResendVerificationEmailController extends Controller
{
    public function __invoke(
        Request $request,
        ResendVerificationEmailService $resendVerificationEmailService
    ) {
        $result = $resendVerificationEmailService->send($request->user());

        return ApiResponse::success(
            data: $result['data'],
            message: $result['message']
        );
    }
}