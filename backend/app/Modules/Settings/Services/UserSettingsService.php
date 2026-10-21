<?php

namespace App\Modules\Settings\Services;

use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Settings\Enums\BudgetMethod;
use App\Modules\Settings\Models\UserSetting;
use Illuminate\Support\Collection;

class UserSettingsService
{
    /** Por petición: varios servicios consultan la configuración del mismo usuario. */
    private array $cache = [];

    /** La fila se crea con los valores por defecto la primera vez que se consulta. */
    public function forUser(int $userId): UserSetting
    {
        return $this->cache[$userId] ??= UserSetting::query()->firstOrCreate(
            ['user_id' => $userId],
            UserSetting::DEFAULTS
        );
    }

    public function update(int $userId, array $data): UserSetting
    {
        $settings = $this->forUser($userId);

        // Al volver a 50/30/20 se conservan los porcentajes personalizados para no perderlos.
        $settings->fill($data)->save();

        return $this->cache[$userId] = $settings->refresh();
    }

    /** Porcentaje de cada bloque (need, want, savings) según el método elegido. */
    public function budgetPercents(int $userId): array
    {
        $settings = $this->forUser($userId);

        if ($settings->budget_method !== BudgetMethod::CUSTOM) {
            return collect(BudgetType::cases())
                ->mapWithKeys(fn (BudgetType $type) => [$type->value => $type->percent()])
                ->all();
        }

        return [
            BudgetType::NEED->value => $settings->budget_need_percent,
            BudgetType::WANT->value => $settings->budget_want_percent,
            BudgetType::SAVINGS->value => $settings->budget_savings_percent,
        ];
    }

    public function preferredCurrency(int $userId): string
    {
        return $this->forUser($userId)->currency;
    }

    /**
     * Moneda para resúmenes (dashboard, presupuesto, reportes): la preferida si el usuario
     * tiene cuentas activas en ella; si no, la de la mayoría de sus cuentas.
     */
    public function summaryCurrency(int $userId, Collection $activeAccounts): string
    {
        $preferred = $this->preferredCurrency($userId);

        if ($activeAccounts->isEmpty() || $activeAccounts->contains('currency', $preferred)) {
            return $preferred;
        }

        return $activeAccounts->countBy('currency')->sortDesc()->keys()->first();
    }
}
