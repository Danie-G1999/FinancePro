<?php

namespace App\Modules\Debts;

use App\Modules\Debts\Interfaces\DebtRepositoryInterface;
use App\Modules\Debts\Repositories\DebtRepository;
use Illuminate\Support\ServiceProvider;

class DebtsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DebtRepositoryInterface::class,
            DebtRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
