<?php

namespace App\Services\Backoffice\Users;

use App\Models\ProfiloCliente;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;

class UpdateUserRolesService
{
    public function update(Utente $user, array $addRoles = [], array $removeRoles = []): Utente
    {
        return DB::transaction(function () use ($user, $addRoles, $removeRoles): Utente {
            foreach ($addRoles as $role) {
                if (! $user->hasRole($role)) {
                    $user->assignRole($role);
                }

                if ($role === 'customer') {
                    $this->ensureCustomerProfile($user);
                }
            }

            foreach ($removeRoles as $role) {
                if ($user->hasRole($role)) {
                    $user->removeRole($role);
                }
            }

            return $user->fresh(['profiloCliente']);
        });
    }

    protected function ensureCustomerProfile(Utente $user): void
    {
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
