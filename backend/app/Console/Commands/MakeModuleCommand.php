<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    /**
     * Nombre y argumentos del comando.
     */
    protected $signature = 'make:module {name : Nombre del módulo}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Crea la estructura base de un módulo de Finance Pro';

    /**
     * Estructura de carpetas que tendrá cada módulo.
     */
    protected array $directories = [
        'Controllers',
        'DTO',
        'Events',
        'Interfaces',
        'Jobs',
        'Listeners',
        'Models',
        'Observers',
        'Policies',
        'Repositories',
        'Requests',
        'Resources',
        'Routes',
        'Services',
        'Tests',

        'database/factories',
        'database/migrations',
        'database/seeders',
    ];

    /**
     * Ejecutar el comando.
     */
    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        $modulePath = app_path("Modules/{$name}");

        if (File::exists($modulePath)) {
            $this->warn("El módulo [{$name}] ya existe.");

            if (! $this->confirm('¿Quieres completar/verificar su estructura?')) {
                $this->info('Operación cancelada.');

                return self::SUCCESS;
            }
        }

        $this->info("Creando módulo [{$name}]...");

        foreach ($this->directories as $directory) {
            $path = "{$modulePath}/{$directory}";

            File::ensureDirectoryExists($path);

            $this->line("  ✓ {$directory}");
        }

        $this->newLine();

        $this->info("Módulo [{$name}] creado correctamente.");

        $this->newLine();

        $this->table(
            ['Propiedad', 'Valor'],
            [
                ['Nombre', $name],
                ['Ruta', $modulePath],
                ['Directorios', count($this->directories)],
            ]
        );

        return self::SUCCESS;
    }
}
