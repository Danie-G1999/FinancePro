<?php

namespace App\Modules\Reports\Services;

use Carbon\CarbonImmutable;

/** Convierte las cifras del reporte en mensajes cortos y accionables. */
class InsightBuilder
{
    private const RECOMMENDED_SAVINGS_RATE = 20;

    private const RECOMMENDED_NEEDS_SHARE = 50;

    private const RECOMMENDED_WANTS_SHARE = 30;

    private const SIGNIFICANT_CHANGE = 5;

    /** @return list<array{type: string, message: string}> */
    public function build(array $report): array
    {
        $currency = $report['currency'];
        $summary = $report['summary'];

        if ($summary['income'] <= 0 && $summary['expenses'] <= 0 && $report['top_expenses'] === []) {
            return [$this->insight('info', 'No hay movimientos en este período. Registra ingresos y gastos para ver tu análisis.')];
        }

        return array_values(array_filter([
            $this->savingsInsight($summary, $currency),
            $this->expenseChangeInsight($summary),
            $this->needsInsight($report['spending']['by_category'], $summary['income']),
            $this->wantsInsight($report['spending']['by_category'], $summary['income']),
            $this->topExpenseInsight($report['top_expenses'], $currency),
            $this->debtInsight($report['debts'], $currency),
            $this->goalInsight($report['goals'], $currency),
        ]));
    }

    private function savingsInsight(array $summary, string $currency): array
    {
        $income = $summary['income'];

        if ($income <= 0) {
            return $this->insight('warning', 'Registraste gastos pero ningún ingreso en este período.');
        }

        $rate = $summary['savings_rate'];

        if ($rate < 0) {
            return $this->insight(
                'danger',
                'Gastaste ' . $this->money(abs($summary['balance']), $currency) . ' más de lo que ingresaste en este período.'
            );
        }

        if ($rate >= self::RECOMMENDED_SAVINGS_RATE) {
            return $this->insight(
                'positive',
                "Ahorraste el {$rate}% de tus ingresos ({$this->money($summary['balance'], $currency)}). Superas el 20% recomendado."
            );
        }

        return $this->insight(
            'warning',
            "Ahorraste el {$rate}% de tus ingresos. Intenta llegar al 20% recomendado."
        );
    }

    private function expenseChangeInsight(array $summary): ?array
    {
        $change = $summary['expenses_change_percent'];

        if ($change === null) {
            return null;
        }

        if (abs($change) < self::SIGNIFICANT_CHANGE) {
            return $this->insight('info', 'Tus gastos se mantienen similares al período anterior.');
        }

        return $change > 0
            ? $this->insight('warning', "Tus gastos subieron {$change}% frente al período anterior.")
            : $this->insight('positive', 'Tus gastos bajaron ' . abs($change) . '% frente al período anterior.');
    }

    private function needsInsight(array $categories, float $income): ?array
    {
        return $this->shareInsight($categories, 'need', $income, self::RECOMMENDED_NEEDS_SHARE, 'necesidades');
    }

    private function wantsInsight(array $categories, float $income): ?array
    {
        return $this->shareInsight($categories, 'want', $income, self::RECOMMENDED_WANTS_SHARE, 'deseos');
    }

    private function shareInsight(array $categories, string $key, float $income, int $limit, string $label): ?array
    {
        if ($income <= 0) {
            return null;
        }

        $share = $categories[$key]['income_share'];

        return $share > $limit
            ? $this->insight('warning', "Tus {$label} usan el {$share}% de tus ingresos (lo recomendado es {$limit}%).")
            : null;
    }

    private function topExpenseInsight(array $topExpenses, string $currency): ?array
    {
        $top = $topExpenses[0] ?? null;

        if (! $top) {
            return null;
        }

        $date = CarbonImmutable::parse($top['date'])->format('d/m');

        return $this->insight(
            'info',
            "Tu mayor gasto fue «{$top['description']}»: {$this->money($top['amount'], $currency)} el {$date}."
        );
    }

    private function debtInsight(array $debts, string $currency): ?array
    {
        if ($debts['remaining'] <= 0) {
            return null;
        }

        return $this->insight(
            'info',
            "Te quedan {$this->money($debts['remaining'], $currency)} por pagar en deudas (llevas {$debts['progress_percent']}% pagado)."
        );
    }

    private function goalInsight(array $goals, string $currency): ?array
    {
        if ($goals['items'] === []) {
            return null;
        }

        return $this->insight(
            'info',
            "Tus metas de ahorro suman {$this->money($goals['saved'], $currency)} de {$this->money($goals['target'], $currency)} ({$goals['progress_percent']}%)."
        );
    }

    private function insight(string $type, string $message): array
    {
        return ['type' => $type, 'message' => $message];
    }

    private function money(float $value, string $currency): string
    {
        $decimals = $currency === 'COP' ? 0 : 2;

        $formatted = '$' . number_format($value, $decimals, ',', '.');

        return $currency === 'COP' ? $formatted : "{$formatted} {$currency}";
    }
}
