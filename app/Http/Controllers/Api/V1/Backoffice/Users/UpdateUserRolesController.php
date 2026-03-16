<?php

namespace App\Http\Controllers\Api\V1\Backoffice\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Backoffice\Users\UpdateUserRolesRequest;
use App\Models\Utente;
use App\Services\Auth\AuthUserPayloadService;
use App\Services\Backoffice\Users\UpdateUserRolesService;
use App\Support\ApiResponse;

class UpdateUserRolesController extends Controller
{
    public function __invoke(
        UpdateUserRolesRequest $request,
        Utente $utente,
        UpdateUserRolesService $updateUserRolesService,
        AuthUserPayloadService $authUserPayloadService
    ) {
        $actor = $request->user('sanctum');

        if (! $actor->hasAnyRole(['superadmin', 'admin'])) {
            return ApiResponse::error('Only a superadmin or an admin can manage user roles.', 403);
        }

        if ($utente->hasRole('superadmin') && ! $actor->hasRole('superadmin')) {
            return ApiResponse::error('Only a superadmin can manage another superadmin.', 403);
        }

        $addRoles = $request->validated('add_roles', []);
        $removeRoles = $request->validated('remove_roles', []);

        $touchesAdminRole = in_array('admin', $addRoles, true) || in_array('admin', $removeRoles, true);

        if (($touchesAdminRole || $utente->hasRole('admin')) && ! $actor->hasRole('superadmin')) {
            return ApiResponse::error('Only a superadmin can assign, remove, or manage the admin role.', 403);
        }

        $user = $updateUserRolesService->update($utente, $addRoles, $removeRoles);

        return ApiResponse::success(
            $authUserPayloadService->build($user),
            'User roles updated successfully.'
        );
    }
}
