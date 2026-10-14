<?php

namespace App\Modules\Income\Console\Commands;

use App\Modules\Income\Services\RecurringIncomeService;
use Illuminate\Console\Command;

class GenerateRecurringIncomes extends Command
{
    protected $signature = 'incomes:generate-recurring';

    protected $description = 'Registra los ingresos recurrentes (salarios) que ya llegaron.';

    public function handle(RecurringIncomeService $service): int
    {
        $created = $service->generateDue();

        $this->info("Ingresos recurrentes registrados: {$created}");

        return self::SUCCESS;
    }
}
