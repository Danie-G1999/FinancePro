<?php

namespace App\Modules\Income;

use App\Modules\Income\Interfaces\IncomeRepositoryInterface;
use App\Modules\Income\Repositories\IncomeRepository;
use Illuminate\Support\ServiceProvider;

class IncomeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IncomeRepositoryInterface::class,
            IncomeRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
