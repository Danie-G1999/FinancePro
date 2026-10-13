<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Los movimientos se leen de incomes y expenses; estos índices aceleran los filtros por cuenta y fecha.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->index(['account_id', 'received_at'], 'incomes_account_date_index');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['account_id', 'spent_at'], 'expenses_account_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex('incomes_account_date_index');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_account_date_index');
        });
    }
};
