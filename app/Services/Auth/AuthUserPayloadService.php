<?php

namespace App\Services\Auth;

use App\Models\Utente;

class AuthUserPayloadService
{
    public function build(Utente $user): array
    {
        $user->loadMissing('profiloCliente');

        $contexts = $this->resolveAvailableContexts($user);

        return [
            'utente' => $this->buildUserPayload($user, $contexts),
            'post_login' => $this->buildPostLoginPayload($user, $contexts),
        ];
    }

    protected function resolveAvailableContexts(Utente $user): array
    {
        $contexts = [];

        if ($this->canAccessAdminContext($user)) {
            $contexts[] = 'admin';
        }

        if ($this->canAccessClienteContext($user)) {
            $contexts[] = 'cliente';
        }

        return $contexts;
    }

    protected function canAccessAdminContext(Utente $user): bool
    {
        if (! $user->hasAnyRole(['amministratore', 'collaboratore'])) {
            return false;
        }

        return in_array((int) $user->stato, [1, 3], true);
    }

    protected function canAccessClienteContext(Utente $user): bool
    {
        if (! $user->hasRole('cliente')) {
            return false;
        }

        if ((int) $user->stato !== 1) {
            return false;
        }

        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        return true;
    }

    protected function buildUserPayload(Utente $user, array $contexts): array
    {
        $roles = $user->getRoleNames()->values()->all();
        $permissions = $user->getAllPermissions()->pluck('name')->values()->all();

        return [
            'id' => $user->id,
            'nome' => $user->nome,
            'cognome' => $user->cognome,
            'email' => $user->email,
            'phone' => $user->phone,
            'stato' => (int) $user->stato,
            'email_verificata' => $user->hasVerifiedEmail(),
            'phone_verificato' => $user->phone_verified_at !== null,
            'ruoli' => $roles,
            'permessi' => $permissions,
            'contesti_disponibili' => $contexts,
            'capabilities' => [
                'access_admin' => in_array('admin', $contexts, true),
                'access_cliente' => in_array('cliente', $contexts, true),
            ],
            'profilo_cliente' => $this->buildProfiloClientePayload($user),
        ];
    }

    protected function buildProfiloClientePayload(Utente $user): ?array
    {
        if (! $user->profiloCliente) {
            return null;
        }

        return [
            'id' => $user->profiloCliente->id,
            'utente_id' => $user->profiloCliente->utente_id,
            'nome' => $user->profiloCliente->nome,
            'email' => $user->profiloCliente->email,
            'id_programma' => $user->profiloCliente->id_programma,
            'data_registrazione' => $user->profiloCliente->data_registrazione,
        ];
    }

    protected function buildPostLoginPayload(Utente $user, array $contexts): array
    {
        if ($user->hasRole('cliente') && ! $user->hasVerifiedEmail()) {
            return [
                'type' => 'verify_email',
                'default_context' => null,
                'available_contexts' => [],
                'switch_enabled' => false,
            ];
        }

        if (count($contexts) === 1) {
            return [
                'type' => 'redirect',
                'default_context' => $contexts[0],
                'available_contexts' => $contexts,
                'switch_enabled' => false,
            ];
        }

        if (count($contexts) > 1) {
            return [
                'type' => 'choose_context',
                'default_context' => null,
                'available_contexts' => $contexts,
                'switch_enabled' => true,
            ];
        }

        return [
            'type' => 'no_context',
            'default_context' => null,
            'available_contexts' => [],
            'switch_enabled' => false,
        ];
    }
}
