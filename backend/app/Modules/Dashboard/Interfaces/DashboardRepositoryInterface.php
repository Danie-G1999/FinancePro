<?php

namespace App\Modules\Dashboard\Interfaces;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    /** Cuentas activas con la suma de ingresos y gastos para calcular el saldo. */
    public function getActiveAccounts(int $userId): Collection;

    /** Deudas con la suma de pagos registrados. */
    public function getDebts(int $userId): Collection;

    /** Ingresos (fecha y valor) en la moneda y rango dados. */
    public function getIncomeEntries(int $userId, string $currency, string $from, string $to): Collection;

    /** Gastos (fecha y valor) en la moneda y rango dados. */
    public function getExpenseEntries(int $userId, string $currency, string $from, string $to): Collection;

    /** Últimos movimientos de ingresos y gastos, ya mezclados y ordenados. */
    public function getRecentMovements(int $userId, int $limit): Collection;
}
