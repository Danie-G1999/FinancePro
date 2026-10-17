<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nulo mientras el usuario no haya terminado o saltado el recorrido guiado.
            $table->timestamp('onboarding_completed_at')
                ->nullable()
                ->after('remember_token');
        });

        // Quienes ya usaban la app no lo necesitan; pueden verlo desde el menú de usuario.
        DB::table('users')->update(['onboarding_completed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_completed_at');
        });
    }
};
