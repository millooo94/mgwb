<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profili_cliente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('utente_id')
                ->unique()
                ->constrained('utenti')
                ->cascadeOnDelete();

            $table->string('codice_cliente', 255)->nullable();

            $table->string('nome', 50)->nullable();
            $table->string('cognome', 250)->nullable();

            $table->string('azienda', 255)->nullable();
            $table->string('tipo_azienda', 150)->nullable();
            $table->string('documento_azienda', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('codice_sdi', 50)->nullable();
            $table->string('email_pec', 100)->nullable();

            $table->string('partita_iva', 25)->nullable();
            $table->string('codice_fiscale', 25)->nullable();

            $table->text('note')->nullable();

            $table->string('sito_web', 50)->nullable();
            $table->dateTime('data_registrazione')->nullable();

            $table->string('lingua', 3)->nullable();
            $table->string('sesso', 1)->nullable();
            $table->date('data_nascita')->nullable();

            $table->string('referente', 50)->nullable();

            $table->tinyInteger('abilita_modifica_prezzi_web')->nullable();
            $table->integer('distributore')->nullable()->default(0);

            $table->tinyInteger('abilita_pagamento_personalizzato')->default(0);

            $table->string('giorni_chiusura', 50)->nullable();
            $table->string('ora_chiusura', 50)->nullable();

            $table->decimal('costo_spedizione', 9, 3)->default(-100.000);

            $table->string('tipo_cliente', 10)->nullable();

            $table->tinyInteger('sincronizzato')->default(0);

            $table->dateTime('data_ultima_modifica_password')->nullable();

            $table->tinyInteger('revcharge')->default(0);

            $table->string('riferimento_cerved', 50)->nullable();

            $table->tinyInteger('quantita_visibile_web')->default(0);

            $table->decimal('fido', 9, 3)->default(0.000);
            $table->decimal('fido_massimo', 9, 3)->default(0.000);

            $table->tinyInteger('blocco_completamento_web')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profili_cliente');
    }
};
