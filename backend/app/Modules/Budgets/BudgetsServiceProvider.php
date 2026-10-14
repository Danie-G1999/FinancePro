<?php

namespace App\Modules\Budgets;

use App\Modules\Budgets\Interfaces\BudgetRepositoryInterface;
use App\Modules\Budgets\Repositories\BudgetRepository;
use Illuminate\Support\ServiceProvider;

class BudgetsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            BudgetRepositoryInterface::class,
            BudgetRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
