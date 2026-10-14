<?php

namespace App\Modules\Income\Enums;

enum RecurringIncomeType: string
{
    case SALARY = 'salary';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SALARY => 'Salario',
            self::OTHER => 'Ingreso recurrente',
        };
    }
}
