<?php

namespace App\Modules\Reports\Services;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Budgets\Enums\CostType;
use App\Modules\Budgets\Interfaces\BudgetRepositoryInterface;
use App\Modules\Debts\Interfaces\DebtRepositoryInterface;
use App\Modules\Goals\Interfaces\GoalRepositoryInterface;
use App\Modules\Reports\Interfaces\ReportRepositoryInterface;
use App\Modules\Settings\Services\UserSettingsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ReportService
{
    private const TOP_EXPENSES = 5;

    public function __construct(
        private ReportRepositoryInterface $repository,
        private BudgetRepositoryInterface $budgets,
        private AccountRepositoryInterface $accounts,
        private DebtRepositoryInterface $debts,
        private GoalRepositoryInterface $goals,
        private InsightBuilder $insights,
        private UserSettingsService $settings
    ) {}

    public function getReport(int $userId, string $from, string $to, ?string $currency): array
    {
        $start = CarbonImmutable::parse($from);
        $end = CarbonImmutable::parse($to);

        $activeAccounts = $this->accounts->getByUser($userId)->where('is_active', true);
        $currencies = $activeAccounts->pluck('currency')->unique()->sort()->values();
        $currency = $currency && $currencies->contains($currency)
            ? $currency
            : $this->settings->summaryCurrency($userId, $activeAccounts);

        $current = $this->periodTotals($userId, $currency, $start, $end);
        $previous = $this->previousTotals($userId, $currency, $start, $end);

        $report = [
            'period' => ['from' => $from, 'to' => $to],
            'currency' => $currency,
            'currencies' => $currencies->all(),
            'summary' => $this->buildSummary($current, $previous),
            'cash_flow' => $this->buildCashFlow($userId, $currency, $start, $end),
            'spending' => $this->buildSpending($current, $this->settings->budgetPercents($userId)),
            'accounts' => $this->buildAccounts($userId, $activeAccounts, $currency, $from, $to),
            'debts' => $this->buildDebts($userId, $currency),
            'goals' => $this->buildGoals($userId, $currency),
            'top_expenses' => $this->buildTopExpenses($userId, $currency, $from, $to),
        ];

        $report['insights'] = $this->insights->build($report);

        return $report;
    }

    /** Ingreso, gasto de consumo (fijo + variable) y aportes a metas del rango. */
    private function periodTotals(int $userId, string $currency, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $from = $start->toDateString();
        $to = $end->toDateString();

        $rows = $this->budgets->expenseTotals($userId, $currency, $from, $to);

        return [
            'income' => $this->budgets->sumIncome($userId, $currency, $from, $to),
            'fixed' => (float) $rows->where('cost_type', CostType::FIXED->value)->sum('total'),
            'variable' => (float) $rows->where('cost_type', CostType::VARIABLE->value)->sum('total'),
            'goal_contributions' => (float) $rows->whereNull('cost_type')->sum('total'),
            'rows' => $rows,
        ];
    }

    /** Período inmediatamente anterior, con la misma duración. */
    private function previousTotals(int $userId, string $currency, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $days = $start->diffInDays($end) + 1;

        $previousEnd = $start->subDay();
        $previousStart = $previousEnd->subDays($days - 1);

        return $this->periodTotals($userId, $currency, $previousStart, $previousEnd);
    }

    private function buildSummary(array $current, array $previous): array
    {
        $expenses = $current['fixed'] + $current['variable'];
        $previousExpenses = $previous['fixed'] + $previous['variable'];
        $balance = $current['income'] - $expenses;

        return [
            'income' => round($current['income'], 2),
            'expenses' => round($expenses, 2),
            'balance' => round($balance, 2),
            'savings_rate' => $current['income'] > 0
                ? round($balance / $current['income'] * 100, 1)
                : null,
            'saved_to_goals' => round($current['goal_contributions'], 2),
            'income_change_percent' => $this->changePercent($current['income'], $previous['income']),
            'expenses_change_percent' => $this->changePercent($expenses, $previousExpenses),
        ];
    }

    private function changePercent(float $current, float $previous): ?float
    {
        return $previous > 0
            ? round(($current - $previous) / $previous * 100, 1)
            : null;
    }

    private function buildCashFlow(int $userId, string $currency, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $from = $start->toDateString();
        $to = $end->toDateString();

        $byMonth = fn (Collection $entries): Collection => $entries
            ->groupBy(fn ($entry) => substr((string) $entry->date, 0, 7))
            ->map(fn (Collection $group) => (float) $group->sum('amount'));

        $incomes = $byMonth($this->repository->getIncomeEntries($userId, $currency, $from, $to));
        $expenses = $byMonth($this->repository->getExpenseEntries($userId, $currency, $from, $to));

        $months = [];

        for ($month = $start->startOfMonth(); $month <= $end; $month = $month->addMonth()) {
            $key = $month->format('Y-m');
            $income = round($incomes[$key] ?? 0, 2);
            $expense = round($expenses[$key] ?? 0, 2);

            $months[] = [
                'month' => $key,
                'label' => ucfirst(rtrim($month->locale('es')->translatedFormat('M'), '.')) . ' ' . $month->format('y'),
                'income' => $income,
                'expense' => $expense,
                'balance' => round($income - $expense, 2),
            ];
        }

        return $months;
    }

    private function buildSpending(array $current, array $percents): array
    {
        $rows = $current['rows'];
        $outflow = (float) $rows->sum('total');
        $income = $current['income'];

        $categories = collect(BudgetType::cases())
            ->mapWithKeys(function (BudgetType $type) use ($rows, $outflow, $income, $percents) {
                $amount = (float) $rows->where('budget_type', $type->value)->sum('total');

                return [$type->value => [
                    'label' => $type->label(),
                    'amount' => round($amount, 2),
                    'share' => $outflow > 0 ? round($amount / $outflow * 100, 1) : 0,
                    'income_share' => $income > 0 ? round($amount / $income * 100, 1) : null,
                    'recommended_percent' => $percents[$type->value],
                ]];
            })
            ->all();

        return [
            'total' => round($outflow, 2),
            'by_category' => $categories,
            'fixed' => round($current['fixed'], 2),
            'variable' => round($current['variable'], 2),
            'goal_contributions' => round($current['goal_contributions'], 2),
        ];
    }

    private function buildAccounts(int $userId, Collection $accounts, string $currency, string $from, string $to): array
    {
        $movements = $this->repository->getTotalsByAccount($userId, $from, $to);

        $items = $accounts->where('currency', $currency)->values();

        $totalBalance = (float) $items->sum(fn ($account) => $account->currentBalance());
        $positiveTotal = (float) $items->sum(fn ($account) => max(0, $account->currentBalance()));

        return [
            'total_balance' => round($totalBalance, 2),
            'items' => $items
                ->map(function ($account) use ($movements, $positiveTotal) {
                    $balance = $account->currentBalance();
                    $moved = $movements[$account->id] ?? ['income' => 0.0, 'expense' => 0.0];

                    return [
                        'id' => $account->id,
                        'name' => $account->name,
                        'type_label' => $account->type?->label(),
                        'institution' => $account->institution,
                        'balance' => $balance,
                        'share_percent' => $positiveTotal > 0 && $balance > 0
                            ? round($balance / $positiveTotal * 100, 1)
                            : 0,
                        'income' => round($moved['income'], 2),
                        'expense' => round($moved['expense'], 2),
                    ];
                })
                ->sortByDesc('balance')
                ->values()
                ->all(),
        ];
    }

    private function buildDebts(int $userId, string $currency): array
    {
        $debts = $this->debts->getByUser($userId)->where('currency', $currency)->values();

        $total = (float) $debts->sum('total_amount');
        $paid = (float) $debts->sum(fn ($debt) => $debt->paidAmount());

        return [
            'total' => round($total, 2),
            'paid' => round($paid, 2),
            'remaining' => round((float) $debts->sum(fn ($debt) => $debt->remainingBalance()), 2),
            'progress_percent' => $total > 0 ? min(100, round($paid / $total * 100, 1)) : 0,
            'items' => $debts
                ->map(fn ($debt) => [
                    'id' => $debt->id,
                    'name' => $debt->name,
                    'total' => (float) $debt->total_amount,
                    'paid' => round($debt->paidAmount(), 2),
                    'remaining' => round($debt->remainingBalance(), 2),
                    'progress_percent' => (float) $debt->total_amount > 0
                        ? min(100, round($debt->paidAmount() / (float) $debt->total_amount * 100, 1))
                        : 0,
                    'paid_installments' => $debt->paidInstallments(),
                    'installments' => $debt->installments,
                ])
                ->sortByDesc('remaining')
                ->values()
                ->all(),
        ];
    }

    private function buildGoals(int $userId, string $currency): array
    {
        $goals = $this->goals->getByUser($userId)->where('currency', $currency)->values();

        $target = (float) $goals->sum('target_amount');
        $saved = (float) $goals->sum(fn ($goal) => $goal->savedAmount());

        return [
            'target' => round($target, 2),
            'saved' => round($saved, 2),
            'progress_percent' => $target > 0 ? min(100, round($saved / $target * 100, 1)) : 0,
            'items' => $goals
                ->map(fn ($goal) => [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target' => (float) $goal->target_amount,
                    'saved' => round($goal->savedAmount(), 2),
                    'progress_percent' => (float) $goal->target_amount > 0
                        ? min(100, round($goal->savedAmount() / (float) $goal->target_amount * 100, 1))
                        : 0,
                    'is_completed' => $goal->remainingAmount() <= 0,
                ])
                ->all(),
        ];
    }

    private function buildTopExpenses(int $userId, string $currency, string $from, string $to): array
    {
        return $this->repository
            ->getTopExpenses($userId, $currency, $from, $to, self::TOP_EXPENSES)
            ->map(fn ($expense) => [
                'id' => $expense->id,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
                'date' => $expense->spent_at->toDateString(),
                'account_name' => $expense->account?->name,
                'category' => $expense->budget_type?->label(),
            ])
            ->all();
    }

}
