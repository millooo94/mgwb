<?php

namespace App\Services\Auth;

use App\Exceptions\ChangePasswordException;
use App\Models\Utente;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function changePassword(Utente $user, array $data): void
    {
        $this->ensureOldPasswordIsValid($user, $data['old_password']);
        $this->updatePassword($user, $data['new_password']);
        $this->revokeUserTokens($user);
    }

    protected function ensureOldPasswordIsValid(Utente $user, string $oldPassword): void
    {
        if (! Hash::check($oldPassword, $user->password)) {
            throw new ChangePasswordException(
                message: 'La password attuale non è corretta.',
                errors: [
                    'old_password' => ['La password attuale non è corretta.'],
                ],
                status: 422
            );
        }
    }

    protected function updatePassword(Utente $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => Hash::make($newPassword),
            'ultimo_cambio_password' => now(),
        ])->save();
    }

    protected function revokeUserTokens(Utente $user): void
    {
        $user->tokens()->delete();
    }
}
