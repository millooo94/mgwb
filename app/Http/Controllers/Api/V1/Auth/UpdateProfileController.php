<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Services\Auth\UpdateProfileService;
use App\Support\ApiResponse;

class UpdateProfileController extends Controller
{
    public function __invoke(
        UpdateProfileRequest $request,
        UpdateProfileService $updateProfileService
    ) {
        $user = $updateProfileService->update(
            user: $request->user(),
            data: $request->validated()
        );

        return ApiResponse::success(
            data: [
                'utente' => [
                    'id' => $user->id,
                    'nome' => $user->nome,
                    'cognome' => $user->cognome,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ],
            message: 'Profilo aggiornato con successo.'
        );
    }
}
