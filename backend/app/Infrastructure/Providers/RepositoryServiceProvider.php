<?php

namespace App\Infrastructure\Providers;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Accounts\Repositories\AccountRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registrar bindings.
     */
    public function register(): void
    {
        $this->app->bind(
            AccountRepositoryInterface::class,
            AccountRepository::class
        );
    }

    /**
     * Inicializar servicios.
     */
    public function boot(): void
    {
        //
    }
}
