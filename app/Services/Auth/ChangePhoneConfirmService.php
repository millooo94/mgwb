<?php

namespace App\Services\Auth;

use App\Exceptions\ChangePhoneException;
use App\Models\AccountContactChange;
use App\Models\Utente;
use App\Services\Notifications\SmsSender;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChangePhoneConfirmService
{
    public function __construct(
        protected SmsSender $smsSender
    ) {}

    public function confirm(Utente $user, array $data): Utente
    {
        $newPhone = PhoneNumberNormalizer::normalize($data['new_phone']);
        $code = trim((string) $data['code']);

        $change = $this->resolvePendingChange($user, $newPhone);

        try {
            $approved = $this->smsSender->checkVerification($newPhone, $code);

            if (! $approved) {
                $change->increment('attempts');

                throw new ChangePhoneException(
                    message: 'Codice non valido o scaduto.',
                    errors: [
                        'code' => ['Codice non valido o scaduto.'],
                    ],
                    status: 422
                );
            }

            return DB::transaction(function () use ($user, $change, $newPhone): Utente {
                $this->ensurePhoneIsStillAvailable($user, $newPhone);

                $user->forceFill([
                    'phone' => $newPhone,
                    'phone_verified_at' => now(),
                ])->save();

                $change->forceFill([
                    'verified_at' => now(),
                ])->save();

                AccountContactChange::query()
                    ->where('utente_id', $user->id)
                    ->where('type', 'phone')
                    ->where('id', '!=', $change->id)
                    ->delete();

                return $user->fresh();
            });
        } catch (ChangePhoneException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new ChangePhoneException(
                message: 'Impossibile confermare la modifica del numero di telefono.',
                errors: [
                    'new_phone' => ['Impossibile confermare la modifica del numero di telefono.'],
                ],
                status: 422
            );
        }
    }

    protected function resolvePendingChange(Utente $user, string $newPhone): AccountContactChange
    {
        $change = AccountContactChange::query()
            ->where('utente_id', $user->id)
            ->where('type', 'phone')
            ->where('new_value', $newPhone)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $change) {
            throw new ChangePhoneException(
                message: 'Nessuna richiesta di cambio numero pendente.',
                errors: [
                    'new_phone' => ['Nessuna richiesta di cambio numero pendente.'],
                ],
                status: 422
            );
        }

        if (! $change->expires_at || $change->expires_at->isPast()) {
            throw new ChangePhoneException(
                message: 'La richiesta di cambio numero è scaduta.',
                errors: [
                    'code' => ['La richiesta di cambio numero è scaduta.'],
                ],
                status: 422
            );
        }

        return $change;
    }

    protected function ensurePhoneIsStillAvailable(Utente $user, string $newPhone): void
    {
        $exists = Utente::query()
            ->where('phone', $newPhone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw new ChangePhoneException(
                message: 'Questo numero di telefono è già associato a un altro account.',
                errors: [
                    'new_phone' => ['Questo numero di telefono è già associato a un altro account.'],
                ],
                status: 422
            );
        }
    }
}
