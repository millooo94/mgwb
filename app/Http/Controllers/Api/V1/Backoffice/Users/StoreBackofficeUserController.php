<?php

namespace App\Http\Controllers\Api\V1\Backoffice\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Backoffice\Users\StoreBackofficeUserRequest;
use App\Services\Auth\AuthUserPayloadService;
use App\Services\Backoffice\Users\CreateBackofficeUserService;
use App\Support\ApiResponse;

class StoreBackofficeUserController extends Controller
{
    public function __invoke(
        StoreBackofficeUserRequest $request,
        CreateBackofficeUserService $createBackofficeUserService,
        AuthUserPayloadService $authUserPayloadService
    ) {
        $actor = $request->user('sanctum');
        $role = $request->validated('role');

        if ($role === 'admin' && ! $actor->hasRole('superadmin')) {
            return ApiResponse::error('Only a superadmin can create an admin user.', 403);
        }

        if ($role === 'staff' && ! $actor->hasAnyRole(['superadmin', 'admin'])) {
            return ApiResponse::error('Only a superadmin or an admin can create a staff user.', 403);
        }

        $user = $createBackofficeUserService->create($request->validated());

        return ApiResponse::success(
            $authUserPayloadService->build($user),
            'Backoffice user created successfully.'
        );
    }
}
