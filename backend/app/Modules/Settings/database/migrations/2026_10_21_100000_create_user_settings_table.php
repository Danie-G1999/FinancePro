<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // Moneda por defecto para registros nuevos y para los resúmenes.
            $table->char('currency', 3)
                ->default('COP');

            // 50_30_20 | custom
            $table->string('budget_method', 20)
                ->default('50_30_20');

            // Solo se usan con el método personalizado; siempre suman 100.
            $table->unsignedTinyInteger('budget_need_percent')
                ->default(50);

            $table->unsignedTinyInteger('budget_want_percent')
                ->default(30);

            $table->unsignedTinyInteger('budget_savings_percent')
                ->default(20);

            $table->boolean('auto_logout_inactive')
                ->default(true);

            $table->unsignedSmallInteger('inactivity_timeout_minutes')
                ->default(5);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
