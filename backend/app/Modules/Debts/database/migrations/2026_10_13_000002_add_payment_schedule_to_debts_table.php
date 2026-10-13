<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->string('payment_frequency', 20)
                ->default('monthly')
                ->after('installments');

            $table->unsignedTinyInteger('payment_day')
                ->default(1)
                ->after('payment_frequency');

            $table->unsignedTinyInteger('second_payment_day')
                ->nullable()
                ->after('payment_day');
        });
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            $table->dropColumn([
                'payment_frequency',
                'payment_day',
                'second_payment_day',
            ]);
        });
    }
};
