<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 60);

            // Bloque de la regla 50/30/20 al que pertenece: need, want o savings.
            $table->string('budget_type', 10);

            $table->string('icon', 16)
                ->nullable();

            $table->char('color', 7)
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'name']);

            $table->index(['user_id', 'budget_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_categories');
    }
};
