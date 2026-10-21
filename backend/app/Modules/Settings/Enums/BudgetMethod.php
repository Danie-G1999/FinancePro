<?php

namespace App\Modules\Settings\Enums;

/** Cómo se reparte el ingreso entre necesidades, deseos y ahorro. */
enum BudgetMethod: string
{
    case RULE_50_30_20 = '50_30_20';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::RULE_50_30_20 => 'Regla 50/30/20',
            self::CUSTOM => 'Porcentajes personalizados',
        };
    }
}
