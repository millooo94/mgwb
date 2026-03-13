<?php

namespace App\Services\Auth;

use App\Exceptions\SetPasswordException;
use App\Models\Utente;
use Illuminate\Support\Facades\Hash;
use Throwable;

class SetPasswordService
{
    public function set(Utente $user, array $data): void
    {
        try {
            if ($user->password !== null) {
                throw new SetPasswordException(
                    message: 'Per questo account è già presente una password. Usa la funzione cambio password.',
                    errors: [
                        'password' => ['Per questo account è già presente una password. Usa la funzione cambio password.'],
                    ],
                    status: 422
                );
            }

            $user->forceFill([
                'password' => Hash::make($data['password']),
                'ultimo_cambio_password' => now(),
            ])->save();
        } catch (SetPasswordException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new SetPasswordException(
                message: 'Impossibile impostare la password.',
                errors: [
                    'password' => ['Impossibile impostare la password.'],
                ],
                status: 422
            );
        }
    }
}
