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
            'user' => $this->buildUserPayload($user, $contexts),
            'post_login' => $this->buildPostLoginPayload($user, $contexts),
        ];
    }

    protected function resolveAvailableContexts(Utente $user): array
    {
        $contexts = [];

        if ($this->canAccessBackofficeContext($user)) {
            $contexts[] = 'backoffice';
        }

        if ($this->canAccessCustomerContext($user)) {
            $contexts[] = 'customer';
        }

        return $contexts;
    }

    protected function canAccessBackofficeContext(Utente $user): bool
    {
        if (! $user->hasAnyRole(['superadmin', 'admin', 'staff'])) {
            return false;
        }

        return in_array((int) $user->stato, [1, 3], true);
    }

    protected function canAccessCustomerContext(Utente $user): bool
    {
        if (! $user->hasRole('customer')) {
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
            'status' => (int) $user->stato,
            'email_verified' => $user->hasVerifiedEmail(),
            'phone_verified' => $user->phone_verified_at !== null,
            'roles' => $roles,
            'permissions' => $permissions,
            'available_contexts' => $contexts,
            'capabilities' => [
                'access_backoffice' => in_array('backoffice', $contexts, true),
                'access_customer' => in_array('customer', $contexts, true),
            ],
            'customer_profile' => $this->buildCustomerProfilePayload($user),
        ];
    }

    protected function buildCustomerProfilePayload(Utente $user): ?array
    {
        if (! $user->profiloCliente) {
            return null;
        }

        return [
            'id' => $user->profiloCliente->id,
            'user_id' => $user->profiloCliente->utente_id,
            'nome' => $user->profiloCliente->nome,
            'email' => $user->profiloCliente->email,
            'program_id' => $user->profiloCliente->id_programma,
            'registered_at' => $user->profiloCliente->data_registrazione,
        ];
    }

    protected function buildPostLoginPayload(Utente $user, array $contexts): array
    {
        $hasBackoffice = in_array('backoffice', $contexts, true);

        if (! $hasBackoffice && $user->hasRole('customer') && ! $user->hasVerifiedEmail()) {
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
