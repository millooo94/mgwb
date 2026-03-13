<?php

namespace App\Services\Auth;

use App\Exceptions\PhoneVerificationException;
use App\Models\Utente;
use App\Services\Notifications\SmsSender;
use App\Support\PhoneNumberNormalizer;
use Throwable;

class PhoneVerificationStartService
{
    public function __construct(
        protected SmsSender $smsSender
    ) {}

    public function start(Utente $user, array $data): array
    {
        $phone = PhoneNumberNormalizer::normalize($data['phone']);
        $currentUserPhone = PhoneNumberNormalizer::normalize($user->phone);

        if ($currentUserPhone === $phone && $user->phone_verified_at !== null) {
            return [
                'already_verified' => true,
                'sent' => false,
                'message' => 'Numero di telefono già verificato.',
            ];
        }

        $this->ensurePhoneIsAvailable($user, $phone);

        try {
            $this->smsSender->sendVerification($phone);
        } catch (Throwable $e) {
            report($e);

            throw new PhoneVerificationException(
                message: $e->getMessage(),
                errors: [
                    'debug' => [$e->getMessage()],
                ],
                status: 422
            );
        }

        return [
            'already_verified' => false,
            'sent' => true,
            'message' => 'Codice di verifica inviato con successo.',
        ];
    }

    protected function ensurePhoneIsAvailable(Utente $user, string $phone): void
    {
        $exists = Utente::query()
            ->where('phone', $phone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new PhoneVerificationException(
                message: 'Questo numero di telefono è già associato a un altro account.',
                errors: [
                    'phone' => ['Questo numero di telefono è già associato a un altro account.'],
                ],
                status: 422
            );
        }
    }
}
