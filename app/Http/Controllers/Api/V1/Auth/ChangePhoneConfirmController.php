<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePhoneConfirmRequest;
use App\Services\Auth\ChangePhoneConfirmService;
use App\Support\ApiResponse;

class ChangePhoneConfirmController extends Controller
{
    public function __invoke(
        ChangePhoneConfirmRequest $request,
        ChangePhoneConfirmService $changePhoneConfirmService
    ) {
        $user = $changePhoneConfirmService->confirm(
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
            message: 'Numero di telefono aggiornato con successo.'
        );
    }
}
