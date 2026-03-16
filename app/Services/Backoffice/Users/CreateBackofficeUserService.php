<?php

namespace App\Services\Backoffice\Users;

use App\Models\AuthIdentity;
use App\Models\ProfiloCliente;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateBackofficeUserService
{
    public function create(array $data): Utente
    {
        return DB::transaction(function () use ($data): Utente {
            $user = Utente::create([
                'nome' => $data['nome'],
                'cognome' => $data['cognome'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'stato' => (int) $data['stato'],
                'email_verified_at' => ! empty($data['email_verified']) ? now() : null,
            ]);

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

            $user->assignRole($data['role']);

            if (! empty($data['assign_customer_role'])) {
                $this->ensureCustomerRoleAndProfile($user);
            }

            return $user->fresh(['profiloCliente']);
        });
    }

    protected function ensureCustomerRoleAndProfile(Utente $user): void
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
