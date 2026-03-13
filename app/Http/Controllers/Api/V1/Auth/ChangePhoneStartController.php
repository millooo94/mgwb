<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePhoneStartRequest;
use App\Services\Auth\ChangePhoneStartService;
use App\Support\ApiResponse;

class ChangePhoneStartController extends Controller
{
    public function __invoke(
        ChangePhoneStartRequest $request,
        ChangePhoneStartService $changePhoneStartService
    ) {
        $result = $changePhoneStartService->start(
            user: $request->user(),
            data: $request->validated()
        );

        return ApiResponse::success(
            data: $result,
            message: 'Codice inviato al nuovo numero di telefono.'
        );
    }
}
