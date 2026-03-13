<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneVerificationCode extends Model
{
    public const PURPOSE_VERIFY_PHONE = 'verify_phone';
    public const PURPOSE_ACCOUNT_RECOVERY = 'account_recovery';

    protected $fillable = [
        'utente_id',
        'phone',
        'purpose',
        'code_hash',
        'attempts',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function utente(): BelongsTo
    {
        return $this->belongsTo(Utente::class, 'utente_id');
    }
}
