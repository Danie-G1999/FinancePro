<?php

namespace App\Modules\Expenses;

use App\Modules\Expenses\Interfaces\ExpenseRepositoryInterface;
use App\Modules\Expenses\Repositories\ExpenseRepository;
use Illuminate\Support\ServiceProvider;

class ExpensesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
