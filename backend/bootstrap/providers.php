<?php

use App\Providers\AppServiceProvider;
use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Modules\Accounts\AccountsServiceProvider;
use App\Modules\Debts\DebtsServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;

return [
    AppServiceProvider::class,

    RepositoryServiceProvider::class,

    AccountsServiceProvider::class,

    DebtsServiceProvider::class,

    AuthServiceProvider::class,
];
