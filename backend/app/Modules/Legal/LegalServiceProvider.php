<?php

namespace App\Modules\Legal;

use Illuminate\Support\ServiceProvider;

class LegalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
