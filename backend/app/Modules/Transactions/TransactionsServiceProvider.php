<?php

namespace App\Modules\Transactions;

use App\Modules\Transactions\Interfaces\TransactionRepositoryInterface;
use App\Modules\Transactions\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class TransactionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
