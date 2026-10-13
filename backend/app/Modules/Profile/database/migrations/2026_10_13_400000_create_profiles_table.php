<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('first_name', 100);

            $table->string('last_name', 100);

            $table->date('birth_date')
                ->nullable();

            $table->string('email', 150);

            $table->string('phone', 20)
                ->nullable();

            $table->string('address', 200)
                ->nullable();

            $table->string('photo_path')
                ->nullable();

            $table->string('employment_type', 20);

            // Solo aplica a independientes.
            $table->boolean('contributes_social_security')
                ->nullable();

            // Solo aplica a empleados.
            $table->string('contract_type', 20)
                ->nullable();

            $table->decimal('monthly_income', 15, 2)
                ->nullable();

            $table->string('family_reference_name', 100)
                ->nullable();

            $table->string('family_reference_relationship', 50)
                ->nullable();

            $table->string('family_reference_phone', 20)
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
