<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Solo la fecha: la contraseña y su hash nunca salen del servidor.
            // Nula en cuentas sin contraseña (Google) o anteriores a este registro.
            $table->timestamp('password_changed_at')
                ->nullable()
                ->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });
    }
};
