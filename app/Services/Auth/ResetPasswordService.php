<?php

namespace App\Services\Auth;

use App\Models\Utente;
use App\Exceptions\ResetPasswordException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordService
{
    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            function (Utente $user, string $password): void {
                $this->updateUserPassword($user, $password);
                $this->revokeUserTokens($user);
                event(new PasswordReset($user));
            }
        );

        $this->ensurePasswordWasReset($status);
    }

    protected function ensurePasswordWasReset(string $status): void
    {
        if ($status !== Password::PASSWORD_RESET) {
            throw new ResetPasswordException(
                message: $this->resolveErrorMessage($status),
                errors: $this->resolveErrors($status),
                status: 422
            );
        }
    }

    protected function updateUserPassword(Utente $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
            'ultimo_cambio_password' => now(),
        ])->save();
    }

    protected function revokeUserTokens(Utente $user): void
    {
        $user->tokens()->delete();
    }

    protected function resolveErrorMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Il link di reset non è valido o è scaduto.',
            Password::INVALID_USER => 'Nessun utente trovato con questa email.',
            default => 'Impossibile reimpostare la password.',
        };
    }

    protected function resolveErrors(string $status): array
    {
        return match ($status) {
            Password::INVALID_TOKEN => [
                'token' => ['Il link di reset non è valido o è scaduto.'],
            ],
            Password::INVALID_USER => [
                'email' => ['Nessun utente trovato con questa email.'],
            ],
            default => [
                'email' => ['Impossibile reimpostare la password.'],
            ],
        };
    }
}
