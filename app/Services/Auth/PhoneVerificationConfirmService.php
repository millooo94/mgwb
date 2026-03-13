<?php

namespace App\Services\Auth;

use App\Exceptions\PhoneVerificationException;
use App\Models\Utente;
use App\Services\Notifications\SmsSender;
use App\Support\PhoneNumberNormalizer;

class PhoneVerificationConfirmService
{
    public function __construct(
        protected SmsSender $smsSender
    ) {}

    public function confirm(Utente $user, array $data): void
    {
        $phone = PhoneNumberNormalizer::normalize($data['phone']);
        $code = $data['code'];
        $currentUserPhone = PhoneNumberNormalizer::normalize($user->phone);

        if ($user->phone_verified_at !== null && $currentUserPhone === $phone) {
            throw new PhoneVerificationException(
                message: 'Numero di telefono già verificato.',
                errors: [
                    'phone' => ['Numero di telefono già verificato.'],
                ],
                status: 422
            );
        }

        $this->ensurePhoneIsStillAvailable($user, $phone);

        $approved = $this->smsSender->checkVerification($phone, $code);

        if (! $approved) {
            throw new PhoneVerificationException(
                message: 'Codice non valido o scaduto.',
                errors: [
                    'code' => ['Codice non valido o scaduto.'],
                ],
                status: 422
            );
        }

        $user->forceFill([
            'phone' => $phone,
            'phone_verified_at' => now(),
        ])->save();
    }

    protected function ensurePhoneIsStillAvailable(Utente $user, string $phone): void
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
