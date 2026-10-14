<?php

namespace App\Modules\Reports;

use App\Modules\Reports\Interfaces\ReportRepositoryInterface;
use App\Modules\Reports\Repositories\ReportRepository;
use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ReportRepositoryInterface::class,
            ReportRepository::class
        );
    }
}
