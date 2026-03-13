<?php

namespace App\Services\Auth;

use App\Exceptions\ChangePhoneException;
use App\Models\AccountContactChange;
use App\Models\Utente;
use App\Services\Notifications\SmsSender;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ChangePhoneStartService
{
    public function __construct(
        protected SmsSender $smsSender
    ) {}

    public function start(Utente $user, array $data): array
    {
        $newPhone = PhoneNumberNormalizer::normalize($data['new_phone']);
        $currentPassword = $data['current_password'] ?? null;

        $this->ensureCanStart($user, $newPhone, $currentPassword);

        try {
            DB::transaction(function () use ($user, $newPhone): void {
                AccountContactChange::query()
                    ->where('utente_id', $user->id)
                    ->where('type', 'phone')
                    ->whereNull('verified_at')
                    ->delete();

                AccountContactChange::create([
                    'utente_id' => $user->id,
                    'type' => 'phone',
                    'new_value' => $newPhone,
                    'sent_to' => $newPhone,
                    'expires_at' => now()->addMinutes(15),
                ]);
            });

            $this->smsSender->sendVerification($newPhone);
        } catch (ChangePhoneException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new ChangePhoneException(
                message: 'Impossibile avviare la modifica del numero di telefono.',
                errors: [
                    'new_phone' => ['Impossibile avviare la modifica del numero di telefono.'],
                ],
                status: 422
            );
        }

        return [
            'sent' => true,
        ];
    }

    protected function ensureCanStart(Utente $user, string $newPhone, mixed $currentPassword): void
    {
        $currentPhone = PhoneNumberNormalizer::normalize($user->phone);

        if ($currentPhone === $newPhone) {
            throw new ChangePhoneException(
                message: 'Il nuovo numero coincide con quello attuale.',
                errors: [
                    'new_phone' => ['Il nuovo numero coincide con quello attuale.'],
                ],
                status: 422
            );
        }

        $exists = Utente::query()
            ->where('phone', $newPhone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new ChangePhoneException(
                message: 'Questo numero di telefono è già associato a un altro account.',
                errors: [
                    'new_phone' => ['Questo numero di telefono è già associato a un altro account.'],
                ],
                status: 422
            );
        }

        if (! $user->password) {
            throw new ChangePhoneException(
                message: 'Per cambiare numero di telefono devi prima impostare una password per il tuo account.',
                errors: [
                    'current_password' => ['Per cambiare numero di telefono devi prima impostare una password per il tuo account.'],
                ],
                status: 422
            );
        }

        if (! is_string($currentPassword) || trim($currentPassword) === '') {
            throw new ChangePhoneException(
                message: 'La password attuale è obbligatoria.',
                errors: [
                    'current_password' => ['La password attuale è obbligatoria.'],
                ],
                status: 422
            );
        }

        if (! Hash::check($currentPassword, $user->password)) {
            throw new ChangePhoneException(
                message: 'La password attuale non è corretta.',
                errors: [
                    'current_password' => ['La password attuale non è corretta.'],
                ],
                status: 422
            );
        }
    }
}
