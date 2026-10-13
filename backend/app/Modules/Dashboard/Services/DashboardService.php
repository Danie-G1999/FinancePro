<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Interfaces\DashboardRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardService
{
    private const MONTHS_IN_CHART = 6;

    private const RECENT_MOVEMENTS = 8;

    private const DEFAULT_CURRENCY = 'COP';

    public function __construct(
        private DashboardRepositoryInterface $repository
    ) {}

    public function getSummary(int $userId): array
    {
        $accounts = $this->repository->getActiveAccounts($userId);
        $debts = $this->repository->getDebts($userId);

        // Los totales se expresan en la moneda con más cuentas activas.
        $currency = $this->resolveCurrency($accounts);

        $today = CarbonImmutable::today();
        $firstMonth = $today->startOfMonth()->subMonths(self::MONTHS_IN_CHART - 1);

        $incomes = $this->repository->getIncomeEntries(
            $userId,
            $currency,
            $firstMonth->toDateString(),
            $today->endOfMonth()->toDateString()
        );

        $expenses = $this->repository->getExpenseEntries(
            $userId,
            $currency,
            $firstMonth->toDateString(),
            $today->endOfMonth()->toDateString()
        );

        $cashFlow = $this->buildCashFlow($firstMonth, $incomes, $expenses);
        $currentMonth = end($cashFlow);

        $totalBalance = $accounts
            ->where('currency', $currency)
            ->sum(fn ($account) => $account->currentBalance());

        $pendingDebt = $debts
            ->where('currency', $currency)
            ->sum(fn ($debt) => $debt->remainingBalance());

        return [
            'currency' => $currency,
            'summary' => [
                'total_balance' => round($totalBalance, 2),
                'month_income' => $currentMonth['income'],
                'month_expense' => $currentMonth['expense'],
                'month_balance' => round($currentMonth['income'] - $currentMonth['expense'], 2),
                'pending_debt' => round($pendingDebt, 2),
                'accounts_count' => $accounts->count(),
                'debts_count' => $debts->where('currency', $currency)->count(),
            ],
            'cash_flow' => $cashFlow,
            'recent_movements' => $this->repository
                ->getRecentMovements($userId, self::RECENT_MOVEMENTS),
        ];
    }

    private function resolveCurrency(Collection $accounts): string
    {
        return $accounts
            ->countBy('currency')
            ->sortDesc()
            ->keys()
            ->first() ?? self::DEFAULT_CURRENCY;
    }

    private function buildCashFlow(
        CarbonImmutable $firstMonth,
        Collection $incomes,
        Collection $expenses
    ): array {
        $totals = fn (Collection $entries): Collection => $entries
            ->groupBy(fn ($entry) => substr((string) $entry->date, 0, 7))
            ->map(fn (Collection $group) => (float) $group->sum('amount'));

        $incomeByMonth = $totals($incomes);
        $expenseByMonth = $totals($expenses);

        $months = [];

        for ($i = 0; $i < self::MONTHS_IN_CHART; $i++) {
            $month = $firstMonth->addMonths($i);
            $key = $month->format('Y-m');

            $months[] = [
                'month' => $key,
                'label' => ucfirst(rtrim($month->locale('es')->translatedFormat('M'), '.')),
                'income' => round($incomeByMonth[$key] ?? 0, 2),
                'expense' => round($expenseByMonth[$key] ?? 0, 2),
            ];
        }

        return $months;
    }
}
