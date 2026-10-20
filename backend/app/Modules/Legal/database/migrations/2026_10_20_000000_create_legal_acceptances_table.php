<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Prueba de la autorización (Decreto 1377 de 2013, art. 7-8): quién aceptó,
        // qué versión, cuándo y desde dónde. Es un historial: nunca se sobrescribe.
        Schema::create('legal_acceptances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // terms | privacy
            $table->string('document', 20);

            $table->string('version', 20);

            $table->timestamp('accepted_at');

            $table->string('ip_address', 45)
                ->nullable();

            $table->string('user_agent', 255)
                ->nullable();

            $table->timestamps();

            $table->index(['user_id', 'document', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_acceptances');
    }
};
