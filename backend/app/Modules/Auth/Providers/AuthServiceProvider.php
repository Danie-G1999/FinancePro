<?php

namespace App\Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Auth\Interfaces\AuthRepositoryInterface;
use App\Modules\Auth\Repositories\AuthRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
