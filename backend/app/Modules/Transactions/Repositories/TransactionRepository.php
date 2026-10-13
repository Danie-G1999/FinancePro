<?php

namespace App\Modules\Transactions\Repositories;

use App\Modules\Transactions\Interfaces\TransactionRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function paginateByUser(int $userId, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = DB::query()
            ->fromSub($this->movements($userId), 'm')
            ->join('accounts', 'accounts.id', '=', 'm.account_id')
            ->leftJoin('debts', 'debts.id', '=', 'm.debt_id')
            ->select([
                'm.id',
                'm.type',
                'm.description',
                'm.amount',
                'm.date',
                'm.notes',
                'm.created_at',
                'm.account_id',
                'accounts.name as account_name',
                'accounts.currency as currency',
                'm.debt_id',
                'debts.name as debt_name',
            ]);

        $this->applyFilters($query, $filters);

        return $query
            ->orderByDesc('m.date')
            ->orderByDesc('m.created_at')
            ->orderByDesc('m.id')
            ->paginate($perPage);
    }

    private function movements(int $userId): Builder
    {
        $incomes = DB::table('incomes')
            ->where('user_id', $userId)
            ->selectRaw("id, 'income' as type, account_id, null as debt_id, description, amount, received_at as date, notes, created_at");

        return DB::table('expenses')
            ->where('user_id', $userId)
            ->selectRaw("id, 'expense' as type, account_id, debt_id, description, amount, spent_at as date, notes, created_at")
            ->unionAll($incomes);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $from) => $q->where('m.date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $to) => $q->where('m.date', '<=', $to))
            ->when($filters['account_id'] ?? null, fn (Builder $q, int $accountId) => $q->where('m.account_id', $accountId))
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);

                $q->whereRaw("m.description LIKE ? ESCAPE '!'", ["%{$escaped}%"]);
            });
    }
}
