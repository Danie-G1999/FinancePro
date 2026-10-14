<?php

namespace App\Modules\Budgets\Enums;

enum CostType: string
{
    case FIXED = 'fixed';
    case VARIABLE = 'variable';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Fijo',
            self::VARIABLE => 'Variable',
        };
    }
}
