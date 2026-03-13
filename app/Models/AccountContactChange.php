<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountContactChange extends Model
{
    protected $fillable = [
        'utente_id',
        'type',
        'new_value',
        'token_hash',
        'code_hash',
        'sent_to',
        'verified_at',
        'expires_at',
        'attempts',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'utente_id');
    }
}
