<?php

namespace App\Modules\Budgets\Interfaces;

use App\Modules\Budgets\Models\Budget;
use Illuminate\Support\Collection;

interface BudgetRepositoryInterface
{
    public function findByUserAndPeriod(int $userId, string $period): ?Budget;

    public function upsert(int $userId, string $period, array $data): Budget;

    public function deleteByUserAndPeriod(int $userId, string $period): void;

    /** Suma de ingresos registrados en la moneda y el rango dados. */
    public function sumIncome(int $userId, string $currency, string $from, string $to): float;

    /**
     * Gastos agrupados por bloque 50/30/20 y tipo de costo.
     *
     * @return Collection<int, object{budget_type: string, cost_type: ?string, total: float}>
     */
    public function expenseTotals(int $userId, string $currency, string $from, string $to): Collection;
}
