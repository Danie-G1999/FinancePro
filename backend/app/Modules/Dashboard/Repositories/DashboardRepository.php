<?php

namespace App\Modules\Dashboard\Repositories;

use App\Modules\Accounts\Models\Account;
use App\Modules\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Modules\Debts\Models\Debt;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Income\Models\Income;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function getActiveAccounts(int $userId): Collection
    {
        return Account::query()
            ->withSum('incomes', 'amount')
            ->withSum('expenses', 'amount')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();
    }

    public function getDebts(int $userId): Collection
    {
        return Debt::query()
            ->withSum('payments', 'amount')
            ->withCount('payments')
            ->where('user_id', $userId)
            ->get();
    }

    public function getIncomeEntries(
        int $userId,
        string $currency,
        string $from,
        string $to
    ): Collection {
        return $this->entries(Income::query(), 'incomes', 'received_at', $userId, $currency, $from, $to);
    }

    public function getExpenseEntries(
        int $userId,
        string $currency,
        string $from,
        string $to
    ): Collection {
        return $this->entries(Expense::query(), 'expenses', 'spent_at', $userId, $currency, $from, $to);
    }

    public function getRecentMovements(int $userId, int $limit): Collection
    {
        $incomes = Income::query()
            ->with('account:id,name,currency')
            ->where('user_id', $userId)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Income $income) => [
                'id' => $income->id,
                'type' => 'income',
                'description' => $income->description,
                'amount' => (float) $income->amount,
                'currency' => $income->account?->currency,
                'account_name' => $income->account?->name,
                'date' => $income->received_at->toDateString(),
                'created_at' => $income->created_at,
            ]);

        $expenses = Expense::query()
            ->with('account:id,name,currency')
            ->where('user_id', $userId)
            ->orderByDesc('spent_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Expense $expense) => [
                'id' => $expense->id,
                'type' => 'expense',
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'currency' => $expense->account?->currency,
                'account_name' => $expense->account?->name,
                'date' => $expense->spent_at->toDateString(),
                'created_at' => $expense->created_at,
            ]);

        return $incomes
            ->concat($expenses)
            ->sortByDesc(fn (array $m) => $m['date'] . ' ' . $m['created_at']->format('H:i:s.u'))
            ->take($limit)
            ->map(fn (array $m) => collect($m)->except('created_at')->all())
            ->values();
    }

    private function entries(
        Builder $query,
        string $table,
        string $dateColumn,
        int $userId,
        string $currency,
        string $from,
        string $to
    ): Collection {
        return $query
            ->join('accounts', 'accounts.id', '=', "{$table}.account_id")
            ->where("{$table}.user_id", $userId)
            ->where('accounts.currency', $currency)
            ->whereBetween("{$table}.{$dateColumn}", [$from, $to])
            ->get(["{$table}.{$dateColumn} as date", "{$table}.amount as amount"]);
    }
}
