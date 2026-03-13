<?php

namespace App\Services\Auth;

use App\Exceptions\UpdateProfileException;
use App\Models\Utente;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateProfileService
{
    public function update(Utente $user, array $data): Utente
    {
        try {
            return DB::transaction(function () use ($user, $data): Utente {
                $user->forceFill([
                    'nome' => $data['nome'],
                    'cognome' => $data['cognome'],
                ])->save();

                if ($user->profiloCliente) {
                    $user->profiloCliente->forceFill([
                        'nome' => $data['nome'],
                        'cognome' => $data['cognome'],
                    ])->save();
                }

                return $user->fresh(['profiloCliente']);
            });
        } catch (UpdateProfileException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new UpdateProfileException(
                message: 'Impossibile aggiornare il profilo.',
                errors: [
                    'profile' => ['Impossibile aggiornare il profilo.'],
                ],
                status: 422
            );
        }
    }
}
