<?php

namespace App\Services\Auth;

use App\Exceptions\LoginException;
use App\Models\Utente;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function __construct(
        protected AuthUserPayloadService $authUserPayloadService,
        protected AccessTokenService $accessTokenService
    ) {
    }

    public function login(array $credentials): array
    {
        $user = $this->resolveUserByCredentials($credentials);

        $token = $this->accessTokenService->create(
            user: $user,
            name: 'access',
            deleteExistingTokens: false,
            abilities: ['*']
        );

        return array_merge(
            ['token' => $token],
            $this->authUserPayloadService->build($user)
        );
    }

    protected function resolveUserByCredentials(array $credentials): Utente
    {
        $user = Utente::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new LoginException(
                message: 'Credenziali non valide.',
                errors: [
                    'email' => ['Credenziali non valide.'],
                ],
                status: 422
            );
        }

        return $user;
    }
}