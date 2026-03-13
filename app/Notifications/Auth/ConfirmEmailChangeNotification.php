<?php

namespace App\Notifications\Auth;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmEmailChangeNotification extends Notification
{
    public function __construct(
        protected string $token
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/') .
            '/confirm-email-change?token=' . urlencode($this->token);

        return (new MailMessage)
            ->subject('Conferma la nuova email')
            ->greeting('Ciao!')
            ->line('Hai richiesto di modificare l’indirizzo email associato al tuo account.')
            ->action('Conferma nuova email', $frontendUrl)
            ->line('Il link scade tra 60 minuti.')
            ->line('Se non hai richiesto tu questa modifica, puoi ignorare questa email.');
    }
}
