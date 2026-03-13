<?php

namespace App\Services\Auth;

use App\Exceptions\AccountRecoveryException;
use App\Models\Utente;
use App\Services\Notifications\SmsSender;
use App\Support\PhoneNumberNormalizer;

class AccountRecoveryVerifyService
{
    public function __construct(
        protected SmsSender $smsSender
    ) {}

    public function verify(array $data): array
    {
        $phone = PhoneNumberNormalizer::normalize($data['phone']);
        $code = $data['code'];

        $user = $this->resolveVerifiedUserByPhone($phone);

        if (! $user) {
            throw $this->invalidCodeException();
        }

        $approved = $this->smsSender->checkVerification($phone, $code);

        if (! $approved) {
            throw $this->invalidCodeException();
        }

        return [
            'masked_email' => $this->maskEmail($user->email),
        ];
    }

    protected function resolveVerifiedUserByPhone(string $phone): ?Utente
    {
        return Utente::query()
            ->where('phone', $phone)
            ->whereNotNull('phone_verified_at')
            ->first();
    }

    protected function maskEmail(string $email): string
    {
        [$localPart, $domain] = explode('@', $email, 2);

        $length = mb_strlen($localPart);

        $start = mb_substr($localPart, 0, 2);
        $end = mb_substr($localPart, -3);

        $maskedLength = max($length - 5, 3);
        $masked = str_repeat('*', $maskedLength);

        return $start . $masked . $end . '@' . $domain;
    }

    protected function invalidCodeException(): AccountRecoveryException
    {
        return new AccountRecoveryException(
            message: 'Codice non valido o scaduto.',
            errors: [
                'code' => ['Codice non valido o scaduto.'],
            ],
            status: 422
        );
    }
}
