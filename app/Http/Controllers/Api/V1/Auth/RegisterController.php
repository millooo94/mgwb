<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Services\Auth\RegisterService;
use App\Support\ApiResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterService $registerService)
    {
        $user = $registerService->register($request->validated());

        return ApiResponse::success(
            data: [
                'utente' => [
                    'id' => $user->id,
                    'nome' => $user->nome,
                    'cognome' => $user->cognome,
                    'email' => $user->email,
                ],
            ],
            message: 'Registrazione completata. Controlla la tua email per verificare l\'account.',
            status: 201
        );
    }
}