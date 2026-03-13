<?php

namespace App\Services\Auth;

use App\Exceptions\ChangeEmailException;
use App\Models\AccountContactChange;
use App\Models\AuthIdentity;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ChangeEmailConfirmService
{
    public function confirm(Utente $user, array $data): Utente
    {
        $token = trim((string) $data['token']);

        $change = $this->resolvePendingChange($user);

        $this->ensureTokenIsValid($change, $token);

        $newEmail = $this->normalizeEmail($change->new_value);

        try {
            return DB::transaction(function () use ($user, $change, $newEmail): Utente {
                $this->ensureEmailIsStillAvailable($user, $newEmail);
                $this->ensureEmailIdentityIsAvailable($user, $newEmail);

                $user->forceFill([
                    'email' => $newEmail,
                    'email_verified_at' => now(),
                ])->save();

                if ($user->profiloCliente) {
                    $user->profiloCliente->forceFill([
                        'email' => $newEmail,
                    ])->save();
                }

                $this->syncEmailIdentity($user, $newEmail);

                $change->forceFill([
                    'verified_at' => now(),
                ])->save();

                AccountContactChange::query()
                    ->where('utente_id', $user->id)
                    ->where('type', 'email')
                    ->where('id', '!=', $change->id)
                    ->delete();

                return $user->fresh(['profiloCliente']);
            });
        } catch (ChangeEmailException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new ChangeEmailException(
                message: 'Impossibile confermare la modifica email.',
                errors: [
                    'email' => ['Impossibile confermare la modifica email.'],
                ],
                status: 422
            );
        }
    }

    protected function resolvePendingChange(Utente $user): AccountContactChange
    {
        $change = AccountContactChange::query()
            ->where('utente_id', $user->id)
            ->where('type', 'email')
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $change) {
            throw new ChangeEmailException(
                message: 'Nessuna richiesta di cambio email pendente.',
                errors: [
                    'token' => ['Nessuna richiesta di cambio email pendente.'],
                ],
                status: 422
            );
        }

        if (! $change->expires_at || $change->expires_at->isPast()) {
            throw new ChangeEmailException(
                message: 'La richiesta di cambio email è scaduta.',
                errors: [
                    'token' => ['La richiesta di cambio email è scaduta.'],
                ],
                status: 422
            );
        }

        return $change;
    }

    protected function ensureTokenIsValid(AccountContactChange $change, string $token): void
    {
        if (! $change->token_hash || ! Hash::check($token, $change->token_hash)) {
            $change->increment('attempts');

            throw new ChangeEmailException(
                message: 'Token di conferma non valido.',
                errors: [
                    'token' => ['Token di conferma non valido.'],
                ],
                status: 422
            );
        }
    }

    protected function ensureEmailIsStillAvailable(Utente $user, string $newEmail): void
    {
        $exists = Utente::query()
            ->whereRaw('LOWER(email) = ?', [$newEmail])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new ChangeEmailException(
                message: 'Questa email è già utilizzata da un altro account.',
                errors: [
                    'token' => ['Questa email è già utilizzata da un altro account.'],
                ],
                status: 422
            );
        }
    }

    protected function ensureEmailIdentityIsAvailable(Utente $user, string $newEmail): void
    {
        $exists = AuthIdentity::query()
            ->where('provider', 'email')
            ->where('provider_user_id', $newEmail)
            ->where('utente_id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new ChangeEmailException(
                message: 'Questa email è già associata a un altro account.',
                errors: [
                    'token' => ['Questa email è già associata a un altro account.'],
                ],
                status: 422
            );
        }
    }

    protected function syncEmailIdentity(Utente $user, string $newEmail): void
    {
        $identity = AuthIdentity::query()
            ->where('utente_id', $user->id)
            ->where('provider', 'email')
            ->first();

        if ($identity) {
            $identity->forceFill([
                'provider_user_id' => $newEmail,
                'provider_email' => $newEmail,
            ])->save();

            return;
        }

        AuthIdentity::create([
            'utente_id' => $user->id,
            'provider' => 'email',
            'provider_user_id' => $newEmail,
            'provider_email' => $newEmail,
        ]);
    }

    protected function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }
}
