<?php

namespace App\Modules\Accounts\Enums;

enum AccountType: string
{
    case CASH = 'cash';
    case BANK = 'bank';
    case CREDIT_CARD = 'credit_card';
    case SAVINGS = 'savings';
    case INVESTMENT = 'investment';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Efectivo',
            self::BANK => 'Cuenta bancaria',
            self::CREDIT_CARD => 'Tarjeta de crédito',
            self::SAVINGS => 'Cuenta de ahorros',
            self::INVESTMENT => 'Inversión',
            self::OTHER => 'Otro',
        };
    }
}
