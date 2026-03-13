<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangeEmailStartRequest;
use App\Services\Auth\ChangeEmailStartService;
use App\Support\ApiResponse;

class ChangeEmailStartController extends Controller
{
    public function __invoke(
        ChangeEmailStartRequest $request,
        ChangeEmailStartService $changeEmailStartService
    ) {
        $result = $changeEmailStartService->start(
            user: $request->user(),
            data: $request->validated()
        );

        return ApiResponse::success(
            data: $result,
            message: 'Ti abbiamo inviato una verifica alla nuova email.'
        );
    }
}
