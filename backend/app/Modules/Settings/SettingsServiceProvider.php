<?php

namespace App\Modules\Settings;

use App\Modules\Settings\Services\UserSettingsService;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Una instancia por petición: comparte la configuración ya consultada entre servicios.
        $this->app->scoped(UserSettingsService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
