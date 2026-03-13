<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthIdentity extends Model
{
    protected $table = 'auth_identities';

    protected $fillable = [
        'utente_id',
        'provider',
        'provider_user_id',
        'provider_email',
    ];

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'utente_id');
    }
}
