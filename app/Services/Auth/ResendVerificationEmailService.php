<?php

namespace App\Services\Auth;

use App\Exceptions\ResendVerificationEmailException;
use App\Models\Utente;
use Throwable;

class ResendVerificationEmailService
{
    public function send(Utente $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'data' => [
                    'already_verified' => true,
                    'sent' => false,
                ],
                'message' => 'Email già verificata.',
            ];
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            throw new ResendVerificationEmailException(
                message: 'Impossibile inviare l\'email di verifica.',
                errors: [
                    'email' => ['Impossibile inviare l\'email di verifica.'],
                ],
                status: 422
            );
        }

        return [
            'data' => [
                'already_verified' => false,
                'sent' => true,
            ],
            'message' => 'Email di verifica inviata.',
        ];
    }
}