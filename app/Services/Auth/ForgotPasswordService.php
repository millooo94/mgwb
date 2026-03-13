<?php

namespace App\Services\Auth;

use App\Exceptions\ForgotPasswordException;
use Illuminate\Support\Facades\Password;

class ForgotPasswordService
{
    public function sendResetLink(array $data): void
    {
        $this->dispatchResetLink($data['email']);
    }

    protected function dispatchResetLink(string $email): void
    {
        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new ForgotPasswordException(
                message: 'Impossibile inviare l\'email di reset password.',
                errors: [
                    'email' => ['Impossibile inviare l\'email di reset password.'],
                ],
                status: 422
            );
        }
    }
}
