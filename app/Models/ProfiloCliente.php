<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfiloCliente extends Model
{
    use HasFactory;

    protected $table = 'profili_cliente';

    protected $fillable = [
        'utente_id',
        'codice_cliente',
        'nome',
        'cognome',
        'azienda',
        'tipo_azienda',
        'documento_azienda',
        'email',
        'codice_sdi',
        'email_pec',
        'partita_iva',
        'codice_fiscale',
        'note',
        'sito_web',
        'data_registrazione',
        'lingua',
        'sesso',
        'data_nascita',
        'referente',
        'abilita_modifica_prezzi_web',
        'distributore',
        'abilita_pagamento_personalizzato',
        'giorni_chiusura',
        'ora_chiusura',
        'costo_spedizione',
        'tipo_cliente',
        'sincronizzato',
        'data_ultima_modifica_password',
        'revcharge',
        'riferimento_cerved',
        'quantita_visibile_web',
        'fido',
        'fido_massimo',
        'blocco_completamento_web',
    ];

    protected $casts = [
        'data_registrazione' => 'datetime',
        'data_nascita' => 'date',
        'data_ultima_modifica_password' => 'datetime',
    ];

    public function utente()
    {
        return $this->belongsTo(Utente::class, 'utente_id');
    }
}
