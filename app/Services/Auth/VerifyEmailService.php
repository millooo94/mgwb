<?php

namespace App\Services\Auth;

use App\Exceptions\VerifyEmailException;
use App\Http\Requests\Api\V1\Auth\VerifyEmailRequest;
use App\Models\Utente;

class VerifyEmailService
{
    public function verify(VerifyEmailRequest $request): string
    {
        $this->ensureRequestSignatureIsValid($request);

        $user = $this->resolveVerifiedUser($request);

        $this->ensureRouteHashMatchesUser($request, $user);

        if ($user->hasVerifiedEmail()) {
            return 'Email già verificata.';
        }

        $user->markEmailAsVerified();

        return 'Email verificata con successo.';
    }

    protected function ensureRequestSignatureIsValid(VerifyEmailRequest $request): void
    {
        if (! $request->hasValidSignature()) {
            throw new VerifyEmailException(
                message: 'Link di verifica non valido o scaduto.',
                errors: [
                    'signature' => ['Link di verifica non valido o scaduto.'],
                ],
                status: 403
            );
        }
    }

    protected function resolveVerifiedUser(VerifyEmailRequest $request): Utente
    {
        $user = $request->verifiedUser();

        if (! $user) {
            throw new VerifyEmailException(
                message: 'Utente non trovato.',
                errors: [
                    'id' => ['Utente non trovato.'],
                ],
                status: 404
            );
        }

        return $user;
    }

    protected function ensureRouteHashMatchesUser(VerifyEmailRequest $request, Utente $user): void
    {
        if (! hash_equals($request->routeHash(), sha1($user->getEmailForVerification()))) {
            throw new VerifyEmailException(
                message: 'Link di verifica non valido.',
                errors: [
                    'hash' => ['Link di verifica non valido.'],
                ],
                status: 403
            );
        }
    }
}