<?php

namespace App\Modules\Profile\Enums;

enum EmploymentType: string
{
    case EMPLOYEE = 'employee';
    case INDEPENDENT = 'independent';

    public function label(): string
    {
        return match ($this) {
            self::EMPLOYEE => 'Empleado',
            self::INDEPENDENT => 'Independiente',
        };
    }
}
