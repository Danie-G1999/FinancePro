<?php

use App\Providers\AppServiceProvider;
use App\Infrastructure\Providers\RepositoryServiceProvider;
use App\Modules\Accounts\AccountsServiceProvider;
use App\Modules\Debts\DebtsServiceProvider;
use App\Modules\Income\IncomeServiceProvider;
use App\Modules\Expenses\ExpensesServiceProvider;
use App\Modules\Dashboard\DashboardServiceProvider;
use App\Modules\Transactions\TransactionsServiceProvider;
use App\Modules\Profile\ProfileServiceProvider;
use App\Modules\Goals\GoalsServiceProvider;
use App\Modules\Budgets\BudgetsServiceProvider;
use App\Modules\Reports\ReportsServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;

return [
    AppServiceProvider::class,

    RepositoryServiceProvider::class,

    AccountsServiceProvider::class,

    DebtsServiceProvider::class,

    IncomeServiceProvider::class,

    ExpensesServiceProvider::class,

    DashboardServiceProvider::class,

    TransactionsServiceProvider::class,

    ProfileServiceProvider::class,

    GoalsServiceProvider::class,

    BudgetsServiceProvider::class,

    ReportsServiceProvider::class,

    AuthServiceProvider::class,
];
