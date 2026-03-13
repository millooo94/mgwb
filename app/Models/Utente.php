<?php

namespace App\Models;

use App\Models\AuthIdentity;
use App\Models\ProfiloCliente;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Utente extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'utenti';

    protected string $guard_name = 'api';

    protected $fillable = [
        'nome',
        'cognome',
        'email',
        'password',
        'phone',
        'stato',
        'ultimo_cambio_password',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'ultimo_cambio_password' => 'datetime',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELAZIONI
    |--------------------------------------------------------------------------
    */

    public function authIdentities()
    {
        return $this->hasMany(AuthIdentity::class, 'utente_id');
    }

    public function profiloCliente()
    {
        return $this->hasOne(ProfiloCliente::class, 'utente_id');
    }



    /*
    |--------------------------------------------------------------------------
    | EMAIL VERIFICATION CUSTOM
    |--------------------------------------------------------------------------
    */

    public function sendEmailVerificationNotification(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        $frontendUrl = rtrim(config('app.frontend_url'), '/') .
            '/verify-email?verify_url=' . urlencode($verificationUrl);

        $this->notify(new class($frontendUrl) extends VerifyEmailNotification {

            public function __construct(private string $frontendUrl) {}

            protected function verificationUrl($notifiable): string
            {
                return $this->frontendUrl;
            }

            public function toMail($notifiable): MailMessage
            {
                return (new MailMessage)
                    ->subject('Verifica il tuo indirizzo email')
                    ->greeting('Ciao!')
                    ->line('Clicca sul pulsante qui sotto per verificare il tuo indirizzo email.')
                    ->action('Verifica email', $this->frontendUrl)
                    ->line('Se non hai creato tu questo account, puoi ignorare questa email.');
            }
        });
    }
}
