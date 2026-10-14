<?php

namespace App\Modules\Budgets\Services;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Budgets\Enums\CostType;
use App\Modules\Budgets\Interfaces\BudgetRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BudgetService
{
    private const DEFAULT_CURRENCY = 'COP';

    private const WARNING_THRESHOLD = 80;

    public function __construct(
        private BudgetRepositoryInterface $repository,
        private AccountRepositoryInterface $accounts
    ) {}

    /** Ingresos, fórmula básica y regla 50/30/20 del mes indicado (YYYY-MM). */
    public function getOverview(int $userId, string $period): array
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $period);
        $from = $start->startOfMonth()->toDateString();
        $to = $start->endOfMonth()->toDateString();

        $budget = $this->repository->findByUserAndPeriod($userId, $period);

        $currency = $budget?->currency ?? $this->dominantCurrency($userId);

        $registered = $this->repository->sumIncome($userId, $currency, $from, $to);
        $planned = $budget ? (float) $budget->net_income : null;
        $income = $planned ?? $registered;

        $totals = $this->repository->expenseTotals($userId, $currency, $from, $to);

        $fixed = $this->sumWhere($totals, 'cost_type', CostType::FIXED->value);
        $variable = $this->sumWhere($totals, 'cost_type', CostType::VARIABLE->value);

        return [
            'period' => $period,
            'currency' => $currency,
            'income' => [
                'amount' => round($income, 2),
                'planned' => $planned,
                'registered' => round($registered, 2),
                'source' => $planned === null ? 'registered' : 'planned',
            ],
            'formula' => [
                'income' => round($income, 2),
                'fixed_expenses' => round($fixed, 2),
                'variable_expenses' => round($variable, 2),
                'surplus' => round($income - $fixed - $variable, 2),
            ],
            'rule' => collect(BudgetType::cases())
                ->mapWithKeys(fn (BudgetType $type) => [
                    $type->value => $this->buildBlock($type, $income, $totals),
                ])
                ->all(),
        ];
    }

    public function save(int $userId, string $period, array $data): array
    {
        $this->repository->upsert($userId, $period, $data);

        return $this->getOverview($userId, $period);
    }

    public function reset(int $userId, string $period): array
    {
        $this->repository->deleteByUserAndPeriod($userId, $period);

        return $this->getOverview($userId, $period);
    }

    private function buildBlock(BudgetType $type, float $income, Collection $totals): array
    {
        $limit = round($income * $type->percent() / 100, 2);
        $spent = round($this->sumWhere($totals, 'budget_type', $type->value), 2);
        $usage = $limit > 0 ? round($spent / $limit * 100, 1) : null;

        return [
            'label' => $type->label(),
            'percent' => $type->percent(),
            'limit' => $limit,
            'spent' => $spent,
            'remaining' => round($limit - $spent, 2),
            'usage_percent' => $usage,
            'status' => match (true) {
                $usage === null => $spent > 0 ? 'exceeded' : 'ok',
                $usage > 100 => 'exceeded',
                $usage >= self::WARNING_THRESHOLD => 'warning',
                default => 'ok',
            },
        ];
    }

    private function sumWhere(Collection $totals, string $column, string $value): float
    {
        return (float) $totals->where($column, $value)->sum('total');
    }

    /** Moneda con más cuentas activas del usuario. */
    private function dominantCurrency(int $userId): string
    {
        return $this->accounts
            ->getByUser($userId)
            ->where('is_active', true)
            ->countBy('currency')
            ->sortDesc()
            ->keys()
            ->first() ?? self::DEFAULT_CURRENCY;
    }
}
