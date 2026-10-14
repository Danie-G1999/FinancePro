<?php

namespace App\Modules\Reports\Interfaces;

use Illuminate\Support\Collection;

interface ReportRepositoryInterface
{
    /** Ingresos (fecha y valor) en la moneda y rango dados. */
    public function getIncomeEntries(int $userId, string $currency, string $from, string $to): Collection;

    /** Gastos de consumo (fecha y valor), sin aportes a metas. */
    public function getExpenseEntries(int $userId, string $currency, string $from, string $to): Collection;

    /** Mayores gastos de consumo del rango. */
    public function getTopExpenses(int $userId, string $currency, string $from, string $to, int $limit): Collection;

    /**
     * Entradas y salidas por cuenta en el rango, indexadas por id de cuenta.
     *
     * @return Collection<int, array{income: float, expense: float}>
     */
    public function getTotalsByAccount(int $userId, string $from, string $to): Collection;
}
