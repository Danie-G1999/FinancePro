<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Bloque de la regla 50/30/20: need, want o savings.
            $table->string('budget_type', 10)
                ->default('want')
                ->after('debt_id');

            // Fijo o variable; nulo en aportes a metas, que no cuentan como gasto de la fórmula.
            $table->string('cost_type', 10)
                ->nullable()
                ->after('budget_type');

            $table->foreignId('goal_id')
                ->nullable()
                ->after('debt_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Los gastos anteriores se clasifican por defecto; los pagos de deuda van a ahorro y deudas.
        DB::table('expenses')->update(['cost_type' => 'variable']);

        DB::table('expenses')
            ->whereNotNull('debt_id')
            ->update(['budget_type' => 'savings', 'cost_type' => 'fixed']);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('goal_id');
            $table->dropColumn(['budget_type', 'cost_type']);
        });
    }
};
