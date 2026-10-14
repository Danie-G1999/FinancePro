<?php

namespace App\Modules\Income;

use App\Modules\Income\Console\Commands\GenerateRecurringIncomes;
use App\Modules\Income\Interfaces\IncomeRepositoryInterface;
use App\Modules\Income\Interfaces\RecurringIncomeRepositoryInterface;
use App\Modules\Income\Repositories\IncomeRepository;
use App\Modules\Income\Repositories\RecurringIncomeRepository;
use Illuminate\Support\ServiceProvider;

class IncomeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IncomeRepositoryInterface::class,
            IncomeRepository::class
        );

        $this->app->bind(
            RecurringIncomeRepositoryInterface::class,
            RecurringIncomeRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([GenerateRecurringIncomes::class]);
        }
    }
}
