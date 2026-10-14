<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_incomes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('description', 150);

            $table->decimal('amount', 15, 2);

            // Salario u otro ingreso recurrente.
            $table->string('type', 20)
                ->default('salary');

            // Día del mes en que llega; en meses más cortos se usa el último día.
            $table->unsignedTinyInteger('day_of_month');

            $table->date('start_date');

            // Último mes (YYYY-MM) para el que ya se registró el ingreso.
            $table->char('last_generated_period', 7)
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('recurring_income_id')
                ->nullable()
                ->after('account_id')
                ->constrained()
                ->nullOnDelete();

            // Evita registrar dos veces el mismo ingreso recurrente en una fecha.
            $table->unique(['recurring_income_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropUnique(['recurring_income_id', 'received_at']);
            $table->dropConstrainedForeignId('recurring_income_id');
        });

        Schema::dropIfExists('recurring_incomes');
    }
};
