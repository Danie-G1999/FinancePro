<?php

namespace App\Modules\Dashboard\Services;

use App\Modules\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Modules\Settings\Services\UserSettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class DashboardService
{
    private const MONTHS_IN_CHART = 6;

    private const RECENT_MOVEMENTS = 8;

    public function __construct(
        private DashboardRepositoryInterface $repository,
        private UserSettingsService $settings
    ) {}

    /**
     * @param  string|null  $period  Mes a consultar (Y-m); por defecto el actual.
     * @param  int|null  $accountId  Limita ingresos, gastos y saldo a una cuenta activa.
     */
    public function getSummary(int $userId, ?string $period = null, ?int $accountId = null): array
    {
        $activeAccounts = $this->repository->getActiveAccounts($userId);
        $debts = $this->repository->getDebts($userId);

        $selectedAccount = $accountId ? $activeAccounts->firstWhere('id', $accountId) : null;
        $accounts = $selectedAccount ? collect([$selectedAccount]) : $activeAccounts;

        // Con una cuenta elegida se usa su moneda; si no, la preferida del usuario
        // (o la de la mayoría de sus cuentas).
        $currency = $selectedAccount?->currency
            ?? $this->settings->summaryCurrency($userId, $activeAccounts);

        $month = $period
            ? CarbonImmutable::createFromFormat('!Y-m', $period)
            : CarbonImmutable::today()->startOfMonth();

        $firstMonth = $month->subMonths(self::MONTHS_IN_CHART - 1);
        $lastDay = $month->endOfMonth()->toDateString();

        $incomes = $this->repository->getIncomeEntries(
            $userId,
            $currency,
            $firstMonth->toDateString(),
            $lastDay,
            $selectedAccount?->id
        );

        $expenses = $this->repository->getExpenseEntries(
            $userId,
            $currency,
            $firstMonth->toDateString(),
            $lastDay,
            $selectedAccount?->id
        );

        $cashFlow = $this->buildCashFlow($firstMonth, $incomes, $expenses);
        $selectedMonth = end($cashFlow);

        $totalBalance = $accounts
            ->where('currency', $currency)
            ->sum(fn ($account) => $account->currentBalance());

        $pendingDebt = $debts
            ->where('currency', $currency)
            ->sum(fn ($debt) => $debt->remainingBalance());

        return [
            'currency' => $currency,
            'filters' => [
                'period' => $month->format('Y-m'),
                'account_id' => $selectedAccount?->id,
            ],
            'accounts' => $activeAccounts
                ->map(fn ($account) => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'currency' => $account->currency,
                ])
                ->values(),
            'summary' => [
                'total_balance' => round($totalBalance, 2),
                'month_income' => $selectedMonth['income'],
                'month_expense' => $selectedMonth['expense'],
                'month_balance' => round($selectedMonth['income'] - $selectedMonth['expense'], 2),
                'pending_debt' => round($pendingDebt, 2),
                'accounts_count' => $accounts->count(),
                'debts_count' => $debts->where('currency', $currency)->count(),
            ],
            'cash_flow' => $cashFlow,
            'recent_movements' => $this->repository->getRecentMovements(
                $userId,
                self::RECENT_MOVEMENTS,
                $month->toDateString(),
                $lastDay,
                $selectedAccount?->id
            ),
        ];
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
