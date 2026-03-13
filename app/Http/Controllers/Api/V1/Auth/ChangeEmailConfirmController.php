<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangeEmailConfirmRequest;
use App\Services\Auth\ChangeEmailConfirmService;
use App\Support\ApiResponse;

class ChangeEmailConfirmController extends Controller
{
    public function __invoke(
        ChangeEmailConfirmRequest $request,
        ChangeEmailConfirmService $changeEmailConfirmService
    ) {
        $user = $changeEmailConfirmService->confirm(
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
            message: 'Email aggiornata con successo.'
        );
    }
}
