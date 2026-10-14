<?php

namespace App\Modules\Budgets\Enums;

/** Bloques de la regla 50/30/20. */
enum BudgetType: string
{
    case NEED = 'need';
    case WANT = 'want';
    case SAVINGS = 'savings';

    public function label(): string
    {
        return match ($this) {
            self::NEED => 'Necesidad',
            self::WANT => 'Deseo',
            self::SAVINGS => 'Ahorro y deudas',
        };
    }

    /** Porcentaje del ingreso neto asignado al bloque. */
    public function percent(): int
    {
        return match ($this) {
            self::NEED => 50,
            self::WANT => 30,
            self::SAVINGS => 20,
        };
    }
}
