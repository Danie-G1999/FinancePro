<?php

use App\Providers\AppServiceProvider;
use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Modules\Accounts\AccountsServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;

return [
    AppServiceProvider::class,

    RepositoryServiceProvider::class,

    AccountsServiceProvider::class,

    AuthServiceProvider::class,
];
