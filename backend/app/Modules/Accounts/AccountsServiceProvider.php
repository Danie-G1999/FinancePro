<?php

namespace App\Modules\Accounts;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Accounts\Repositories\AccountRepository;
use Illuminate\Support\ServiceProvider;

class AccountsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AccountRepositoryInterface::class,
            AccountRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
