<?php

namespace App\Modules\Dashboard\Interfaces;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    /** Cuentas activas con la suma de ingresos y gastos para calcular el saldo. */
    public function getActiveAccounts(int $userId): Collection;

    /** Deudas con la suma de pagos registrados. */
    public function getDebts(int $userId): Collection;

    /** Ingresos (fecha y valor) en la moneda y rango dados, opcionalmente de una sola cuenta. */
    public function getIncomeEntries(int $userId, string $currency, string $from, string $to, ?int $accountId = null): Collection;

    /** Gastos (fecha y valor) en la moneda y rango dados, opcionalmente de una sola cuenta. */
    public function getExpenseEntries(int $userId, string $currency, string $from, string $to, ?int $accountId = null): Collection;

    /** Últimos movimientos de ingresos y gastos del rango dado, ya mezclados y ordenados. */
    public function getRecentMovements(int $userId, int $limit, string $from, string $to, ?int $accountId = null): Collection;
}
