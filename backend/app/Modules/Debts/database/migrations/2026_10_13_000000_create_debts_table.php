<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 100);

            $table->string('type', 30);

            $table->string('creditor', 150)
                ->nullable();

            $table->decimal('total_amount', 15, 2);

            $table->decimal('paid_amount', 15, 2)
                ->default(0);

            $table->decimal('interest_rate', 5, 2)
                ->nullable();

            $table->decimal('monthly_payment', 15, 2)
                ->nullable();

            $table->char('currency', 3)
                ->default('COP');

            $table->date('due_date')
                ->nullable();

            $table->text('description')
                ->nullable();

            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
