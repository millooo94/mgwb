<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_contact_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('utente_id')
                ->constrained('utenti')
                ->cascadeOnDelete();

            $table->string('type', 20); // email | phone
            $table->string('new_value', 191);

            $table->string('token_hash', 255)->nullable();
            $table->string('code_hash', 255)->nullable();

            $table->string('sent_to', 191)->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->unsignedSmallInteger('attempts')->default(0);

            $table->timestamps();

            $table->index(['utente_id', 'type']);
            $table->index(['type', 'new_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_contact_changes');
    }
};
