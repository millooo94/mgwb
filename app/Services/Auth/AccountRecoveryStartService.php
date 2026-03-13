<?php

namespace App\Services\Auth;

use App\Exceptions\AccountRecoveryException;
use App\Models\Utente;
use App\Services\Notifications\SmsSender;
use App\Support\PhoneNumberNormalizer;
use Throwable;

class AccountRecoveryStartService
{
    public function __construct(
        protected SmsSender $smsSender
    ) {}

    public function start(array $data): array
    {
        $phone = PhoneNumberNormalizer::normalize($data['phone']);

        $user = $this->resolveVerifiedUserByPhone($phone);

        if (! $user) {
            return $this->buildGenericResponse();
        }

        try {
            $this->smsSender->sendVerification($phone);
        } catch (Throwable $e) {
            report($e);

            throw new AccountRecoveryException(
                message: 'Impossibile inviare il codice di recupero.',
                errors: [
                    'phone' => ['Impossibile inviare il codice di recupero.'],
                ],
                status: 422
            );
        }

        return $this->buildGenericResponse();
    }

    protected function resolveVerifiedUserByPhone(string $phone): ?Utente
    {
        return Utente::query()
            ->where('phone', $phone)
            ->whereNotNull('phone_verified_at')
            ->first();
    }

    protected function buildGenericResponse(): array
    {
        return [
            'sent' => true,
            'message' => 'Se esiste un account associato a questo numero, abbiamo inviato le istruzioni.',
        ];
    }
}
