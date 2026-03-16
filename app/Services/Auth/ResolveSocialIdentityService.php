<?php

namespace App\Services\Auth;

use App\Models\AuthIdentity;
use App\Models\ProfiloCliente;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;

class ResolveSocialIdentityService
{
    public function resolve(array $data): Utente
    {
        return DB::transaction(function () use ($data): Utente {
            $provider = (string) $data['provider'];
            $providerUserId = (string) $data['provider_user_id'];
            $email = mb_strtolower(trim((string) $data['email']));
            $nome = trim((string) ($data['nome'] ?? ''));
            $cognome = trim((string) ($data['cognome'] ?? ''));

            $shouldAttachCustomerRole = array_key_exists('should_attach_customer_role', $data)
                ? (is_null($data['should_attach_customer_role']) ? null : (bool) $data['should_attach_customer_role'])
                : null;

            $identity = AuthIdentity::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            $wasNewUser = false;

            if ($identity) {
                $user = $identity->utente;
            } else {
                $user = Utente::query()
                    ->where('email', $email)
                    ->first();

                if (! $user) {
                    $user = Utente::create([
                        'nome' => $nome !== '' ? $nome : explode('@', $email)[0],
                        'cognome' => $cognome !== '' ? $cognome : '-',
                        'email' => $email,
                        'password' => null,
                        'stato' => 1,
                        'email_verified_at' => now(),
                    ]);

                    $wasNewUser = true;
                }
            }

            $this->refreshUserBaseData(
                user: $user,
                providerEmail: $email,
                nome: $nome,
                cognome: $cognome
            );

            AuthIdentity::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'utente_id' => $user->id,
                    'provider_email' => $email,
                ]
            );

            $this->ensureEmailIdentity($user);

            if ($this->shouldEnsureCustomerBaseline($user, $wasNewUser, $shouldAttachCustomerRole)) {
                $this->ensureCustomerBaseline($user);
            }

            return $user->fresh(['profiloCliente']);
        });
    }

    protected function refreshUserBaseData(
        Utente $user,
        string $providerEmail,
        string $nome,
        string $cognome
    ): void {
        $updates = [];

        if (
            mb_strtolower(trim((string) $user->email)) === $providerEmail
            && ! $user->email_verified_at
        ) {
            $updates['email_verified_at'] = now();
        }

        if ($nome !== '' && $this->isMissingPersonField($user->nome)) {
            $updates['nome'] = $nome;
        }

        if ($cognome !== '' && $this->isMissingPersonField($user->cognome)) {
            $updates['cognome'] = $cognome;
        }

        if (! empty($updates)) {
            $user->update($updates);
        }
    }

    protected function isMissingPersonField(?string $value): bool
    {
        $value = trim((string) $value);

        return $value === '' || $value === '-';
    }

    protected function ensureEmailIdentity(Utente $user): void
    {
        AuthIdentity::firstOrCreate(
            [
                'provider' => 'email',
                'provider_user_id' => mb_strtolower($user->email),
            ],
            [
                'utente_id' => $user->id,
                'provider_email' => mb_strtolower($user->email),
            ]
        );
    }

    protected function shouldEnsureCustomerBaseline(
        Utente $user,
        bool $wasNewUser,
        ?bool $shouldAttachCustomerRole
    ): bool {
        if ($shouldAttachCustomerRole === true) {
            return true;
        }

        if ($shouldAttachCustomerRole === false) {
            return false;
        }

        if ($wasNewUser) {
            return true;
        }

        if ($user->hasRole('customer')) {
            return true;
        }

        if ($user->profiloCliente()->exists()) {
            return true;
        }

        if ($user->hasAnyRole(['superadmin', 'admin', 'staff'])) {
            return false;
        }

        return $user->roles()->doesntExist();
    }

    protected function ensureCustomerBaseline(Utente $user): void
    {
        if (! $user->hasRole('customer')) {
            $user->assignRole('customer');
        }

        ProfiloCliente::firstOrCreate(
            [
                'utente_id' => $user->id,
            ],
            [
                'id_programma' => 1,
                'email' => $user->email,
                'nome' => $user->nome ?: $user->email,
                'data_registrazione' => now(),
            ]
        );
    }
}
