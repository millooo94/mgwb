<?php

namespace App\Services\Auth;

use App\Models\Utente;

class AccessTokenService
{
    public function create(
        Utente $user,
        string $name = 'access',
        bool $deleteExistingTokens = false,
        array $abilities = ['*']
    ): string {
        if ($deleteExistingTokens) {
            $user->tokens()->delete();
        }

        return $user->createToken($name, $abilities)->plainTextToken;
    }
}