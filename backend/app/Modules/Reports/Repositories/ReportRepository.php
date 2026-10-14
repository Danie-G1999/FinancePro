<?php

namespace App\Modules\Reports\Repositories;

use App\Modules\Expenses\Models\Expense;
use App\Modules\Reports\Interfaces\ReportRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportRepository implements ReportRepositoryInterface
{
    public function getIncomeEntries(int $userId, string $currency, string $from, string $to): Collection
    {
        return DB::table('incomes')
            ->join('accounts', 'accounts.id', '=', 'incomes.account_id')
            ->where('incomes.user_id', $userId)
            ->where('accounts.currency', $currency)
            ->whereBetween('incomes.received_at', [$from, $to])
            ->get(['incomes.received_at as date', 'incomes.amount as amount']);
    }

    public function getExpenseEntries(int $userId, string $currency, string $from, string $to): Collection
    {
        return DB::table('expenses')
            ->join('accounts', 'accounts.id', '=', 'expenses.account_id')
            ->where('expenses.user_id', $userId)
            ->where('accounts.currency', $currency)
            ->whereNull('expenses.goal_id')
            ->whereBetween('expenses.spent_at', [$from, $to])
            ->get(['expenses.spent_at as date', 'expenses.amount as amount']);
    }

    public function getTopExpenses(int $userId, string $currency, string $from, string $to, int $limit): Collection
    {
        return Expense::query()
            ->with('account:id,name,currency')
            ->where('user_id', $userId)
            ->whereNull('goal_id')
            ->whereBetween('spent_at', [$from, $to])
            ->whereHas('account', fn ($query) => $query->where('currency', $currency))
            ->orderByDesc('amount')
            ->orderByDesc('spent_at')
            ->limit($limit)
            ->get();
    }

    public function getTotalsByAccount(int $userId, string $from, string $to): Collection
    {
        $incomes = DB::table('incomes')
            ->where('user_id', $userId)
            ->whereBetween('received_at', [$from, $to])
            ->groupBy('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->pluck('total', 'account_id');

        $expenses = DB::table('expenses')
            ->where('user_id', $userId)
            ->whereBetween('spent_at', [$from, $to])
            ->groupBy('account_id')
            ->selectRaw('account_id, SUM(amount) as total')
            ->pluck('total', 'account_id');

        return $incomes->keys()
            ->merge($expenses->keys())
            ->unique()
            ->mapWithKeys(fn ($accountId) => [
                $accountId => [
                    'income' => (float) ($incomes[$accountId] ?? 0),
                    'expense' => (float) ($expenses[$accountId] ?? 0),
                ],
            ]);
    }
}
