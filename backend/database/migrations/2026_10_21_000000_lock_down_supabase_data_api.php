<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| Supabase publica el esquema "public" en su API REST (/rest/v1) para los roles
| "anon" y "authenticated". Las tablas que crea Laravel nacen sin RLS y con permisos
| para esos roles, así que cualquiera con la anon key (pública por diseño) podía leer,
| modificar o borrar usuarios, sesiones y finanzas sin pasar por Laravel.
|
| Esta app solo accede a la base desde Laravel (rol "postgres", que tiene BYPASSRLS),
| así que se cierra la API de Supabase por completo:
|   1. RLS activado en todas las tablas y sin políticas => anon/authenticated no ven nada.
|   2. Se retiran sus permisos, también para las tablas que se creen en el futuro.
*/
return new class extends Migration
{
    private const API_ROLES = 'anon, authenticated';

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! $this->apiRolesExist()) {
            return;
        }

        foreach ($this->publicTables() as $table) {
            DB::statement(sprintf('ALTER TABLE public.%s ENABLE ROW LEVEL SECURITY', $this->quote($table)));
        }

        $roles = self::API_ROLES;

        DB::statement("REVOKE ALL ON ALL TABLES IN SCHEMA public FROM {$roles}");
        DB::statement("REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM {$roles}");
        DB::statement("REVOKE ALL ON ALL FUNCTIONS IN SCHEMA public FROM {$roles}");

        // Tablas futuras creadas por las migraciones (rol postgres) nacen ya sin permisos.
        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public REVOKE ALL ON TABLES FROM {$roles}");
        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public REVOKE ALL ON SEQUENCES FROM {$roles}");
        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public REVOKE ALL ON FUNCTIONS FROM {$roles}");
    }

    public function down(): void
    {
        // Volver a abrir la base a la API pública sería reintroducir la vulnerabilidad:
        // solo se desactiva RLS; los permisos de anon/authenticated no se restauran.
        if (DB::getDriverName() !== 'pgsql' || ! $this->apiRolesExist()) {
            return;
        }

        foreach ($this->publicTables() as $table) {
            DB::statement(sprintf('ALTER TABLE public.%s DISABLE ROW LEVEL SECURITY', $this->quote($table)));
        }
    }

    /** Solo aplica en Supabase (u otro Postgres con esos roles). */
    private function apiRolesExist(): bool
    {
        return DB::table('pg_roles')->whereIn('rolname', ['anon', 'authenticated'])->count() === 2;
    }

    private function publicTables(): array
    {
        return DB::table('pg_tables')
            ->where('schemaname', 'public')
            ->pluck('tablename')
            ->all();
    }

    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
};
