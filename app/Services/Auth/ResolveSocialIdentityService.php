<?php

namespace App\Services\Auth;

use App\Models\AuthIdentity;
use App\Models\ProfiloCliente;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResolveSocialIdentityService
{
    public function resolve(array $data): Utente
    {
        $provider = (string) $data['provider'];
        $providerUserId = (string) $data['provider_user_id'];
        $providerEmail = $this->normalizeEmail($data['email'] ?? null);

        $identity = AuthIdentity::query()
            ->with('utente')
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($identity && $identity->utente) {
            $this->syncExistingIdentity($identity, $providerEmail);

            return $identity->utente->fresh();
        }

        return DB::transaction(function () use ($provider, $providerUserId, $providerEmail, $data): Utente {
            $user = null;

            if ($providerEmail) {
                $user = Utente::query()
                    ->whereRaw('LOWER(email) = ?', [$providerEmail])
                    ->first();
            }

            if (! $user) {
                [$nome, $cognome] = $this->resolveNames($data, $providerEmail);

                $user = Utente::create([
                    'nome' => $nome,
                    'cognome' => $cognome,
                    'email' => $providerEmail,
                    'password' => Hash::make(Str::random(40)),
                    'stato' => 1,
                    'email_verified_at' => $providerEmail ? now() : null,
                ]);

                $user->assignRole('cliente');

                ProfiloCliente::create([
                    'utente_id' => $user->id,
                    'id_programma' => 1,
                    'email' => $user->email,
                    'nome' => trim($nome . ' ' . $cognome),
                    'data_registrazione' => now(),
                ]);
            } else {
                $this->syncExistingUser($user, $providerEmail);
            }

            AuthIdentity::firstOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'utente_id' => $user->id,
                    'provider_email' => $providerEmail,
                ]
            );

            return $user->fresh();
        });
    }

    protected function syncExistingIdentity(AuthIdentity $identity, ?string $providerEmail): void
    {
        $dirtyIdentity = false;
        $user = $identity->utente;
        $dirtyUser = false;

        if ($providerEmail && ! $identity->provider_email) {
            $identity->provider_email = $providerEmail;
            $dirtyIdentity = true;
        }

        if ($providerEmail && $user && ! $user->email_verified_at) {
            $user->email_verified_at = now();
            $dirtyUser = true;
        }

        if ($dirtyIdentity) {
            $identity->save();
        }

        if ($dirtyUser) {
            $user->save();
        }
    }

    protected function syncExistingUser(Utente $user, ?string $providerEmail): void
    {
        $dirty = false;

        if ($providerEmail && ! $user->email_verified_at) {
            $user->email_verified_at = now();
            $dirty = true;
        }

        if ($dirty) {
            $user->save();
        }
    }

    protected function resolveNames(array $data, ?string $email): array
    {
        $fallback = $this->fallbackNameFromEmail($email);

        $nome = trim((string) ($data['nome'] ?? ''));
        $cognome = trim((string) ($data['cognome'] ?? ''));

        return [
            $nome !== '' ? $nome : $fallback,
            $cognome !== '' ? $cognome : '-',
        ];
    }

    protected function fallbackNameFromEmail(?string $email): string
    {
        if (! $email) {
            return 'Utente';
        }

        return explode('@', $email)[0] ?: 'Utente';
    }

    protected function normalizeEmail(mixed $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }

        $email = mb_strtolower(trim($email));

        return $email !== '' ? $email : null;
    }
}
