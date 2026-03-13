<?php

namespace App\Services\Auth;

use App\Exceptions\RegisterException;
use App\Models\AuthIdentity;
use App\Models\ProfiloCliente;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class RegisterService
{
    public function register(array $data): Utente
    {
        try {
            return DB::transaction(function () use ($data): Utente {
                $user = $this->createUser($data);

                $this->createEmailIdentity($user);

                $this->assignDefaultRole($user);

                $this->createClienteProfile($user);

                $this->sendVerificationEmail($user);

                return $user;
            });
        } catch (RegisterException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RegisterException(
                message: $e->getMessage(),
                errors: [
                    'debug' => [$e->getMessage()],
                ],
                status: 422
            );
        }
    }

    protected function createUser(array $data): Utente
    {
        return Utente::create([
            'nome' => $data['nome'],
            'cognome' => $data['cognome'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'stato' => 1,
        ]);
    }

    protected function createEmailIdentity(Utente $user): void
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

    protected function assignDefaultRole(Utente $user): void
    {
        $user->assignRole('cliente');
    }

    protected function createClienteProfile(Utente $user): void
    {
        ProfiloCliente::create([
            'utente_id' => $user->id,
            'id_programma' => 1,
            'email' => $user->email,
            'nome' => $user->nome,
            'data_registrazione' => now(),
        ]);
    }

    protected function sendVerificationEmail(Utente $user): void
    {
        $user->sendEmailVerificationNotification();
    }
}
