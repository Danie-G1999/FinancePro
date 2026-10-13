<?php

namespace App\Modules\Debts\Enums;

enum DebtType: string
{
    case CREDIT_CARD = 'credit_card';
    case PERSONAL_LOAN = 'personal_loan';
    case MORTGAGE = 'mortgage';
    case VEHICLE = 'vehicle';
    case STUDENT = 'student';
    case INFORMAL = 'informal';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Tarjeta de crédito',
            self::PERSONAL_LOAN => 'Préstamo personal',
            self::MORTGAGE => 'Hipoteca',
            self::VEHICLE => 'Vehículo',
            self::STUDENT => 'Crédito educativo',
            self::INFORMAL => 'Préstamo informal',
            self::OTHER => 'Otro',
        };
    }
}
