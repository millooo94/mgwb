<?php

namespace App\Services\Auth;

use App\Exceptions\ChangeEmailException;
use App\Models\AccountContactChange;
use App\Models\Utente;
use App\Notifications\Auth\ConfirmEmailChangeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class ChangeEmailStartService
{
    public function start(Utente $user, array $data): array
    {
        $newEmail = $this->normalizeEmail($data['new_email']);
        $currentPassword = $data['current_password'] ?? null;

        $this->ensureCanStart($user, $newEmail, $currentPassword);

        $plainToken = Str::random(64);

        try {
            DB::transaction(function () use ($user, $newEmail, $plainToken): void {
                AccountContactChange::query()
                    ->where('utente_id', $user->id)
                    ->where('type', 'email')
                    ->whereNull('verified_at')
                    ->delete();

                AccountContactChange::create([
                    'utente_id' => $user->id,
                    'type' => 'email',
                    'new_value' => $newEmail,
                    'token_hash' => Hash::make($plainToken),
                    'sent_to' => $newEmail,
                    'expires_at' => now()->addMinutes(60),
                ]);
            });

            Notification::route('mail', $newEmail)
                ->notify(new ConfirmEmailChangeNotification($plainToken));
        } catch (ChangeEmailException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new ChangeEmailException(
                message: 'Impossibile avviare la modifica email.',
                errors: [
                    'email' => ['Impossibile avviare la modifica email.'],
                ],
                status: 422
            );
        }

        return [
            'sent' => true,
        ];
    }

    protected function ensureCanStart(Utente $user, string $newEmail, mixed $currentPassword): void
    {
        if ($this->normalizeEmail($user->email) === $newEmail) {
            throw new ChangeEmailException(
                message: 'La nuova email coincide con quella attuale.',
                errors: [
                    'new_email' => ['La nuova email coincide con quella attuale.'],
                ],
                status: 422
            );
        }

        $exists = Utente::query()
            ->whereRaw('LOWER(email) = ?', [$newEmail])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new ChangeEmailException(
                message: 'Questa email è già utilizzata da un altro account.',
                errors: [
                    'new_email' => ['Questa email è già utilizzata da un altro account.'],
                ],
                status: 422
            );
        }

        if (! $user->password) {
            throw new ChangeEmailException(
                message: 'Per cambiare email devi prima impostare una password per il tuo account.',
                errors: [
                    'current_password' => ['Per cambiare email devi prima impostare una password per il tuo account.'],
                ],
                status: 422
            );
        }

        if (! is_string($currentPassword) || trim($currentPassword) === '') {
            throw new ChangeEmailException(
                message: 'La password attuale è obbligatoria.',
                errors: [
                    'current_password' => ['La password attuale è obbligatoria.'],
                ],
                status: 422
            );
        }

        if (! Hash::check($currentPassword, $user->password)) {
            throw new ChangeEmailException(
                message: 'La password attuale non è corretta.',
                errors: [
                    'current_password' => ['La password attuale non è corretta.'],
                ],
                status: 422
            );
        }
    }

    protected function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }
}
