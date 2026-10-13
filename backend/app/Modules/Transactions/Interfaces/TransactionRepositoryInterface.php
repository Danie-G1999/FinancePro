<?php

namespace App\Modules\Transactions\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface TransactionRepositoryInterface
{
    /**
     * Movimientos (ingresos y gastos) del usuario, del más reciente al más antiguo.
     *
     * @param  array{date_from?: ?string, date_to?: ?string, account_id?: ?int, search?: ?string}  $filters
     */
    public function paginateByUser(int $userId, array $filters, int $perPage): LengthAwarePaginator;
}
