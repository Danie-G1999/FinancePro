<?php

namespace App\Modules\Budgets\Repositories;

use App\Modules\Budgets\Interfaces\BudgetRepositoryInterface;
use App\Modules\Budgets\Models\Budget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetRepository implements BudgetRepositoryInterface
{
    public function __construct(
        private Budget $model
    ) {}

    public function findByUserAndPeriod(int $userId, string $period): ?Budget
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->where('period', $period)
            ->first();
    }

    public function upsert(int $userId, string $period, array $data): Budget
    {
        return $this->model
            ->newQuery()
            ->updateOrCreate(
                ['user_id' => $userId, 'period' => $period],
                $data
            );
    }

    public function deleteByUserAndPeriod(int $userId, string $period): void
    {
        $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->where('period', $period)
            ->delete();
    }

    public function sumIncome(int $userId, string $currency, string $from, string $to): float
    {
        return (float) DB::table('incomes')
            ->join('accounts', 'accounts.id', '=', 'incomes.account_id')
            ->where('incomes.user_id', $userId)
            ->where('accounts.currency', $currency)
            ->whereBetween('incomes.received_at', [$from, $to])
            ->sum('incomes.amount');
    }

    public function expenseTotals(int $userId, string $currency, string $from, string $to): Collection
    {
        return DB::table('expenses')
            ->join('accounts', 'accounts.id', '=', 'expenses.account_id')
            ->where('expenses.user_id', $userId)
            ->where('accounts.currency', $currency)
            ->whereBetween('expenses.spent_at', [$from, $to])
            ->groupBy('expenses.budget_type', 'expenses.cost_type')
            ->selectRaw('expenses.budget_type as budget_type, expenses.cost_type as cost_type, SUM(expenses.amount) as total')
            ->get();
    }
}
