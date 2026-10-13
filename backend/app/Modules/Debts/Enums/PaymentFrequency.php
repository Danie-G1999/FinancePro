<?php

namespace App\Modules\Debts\Enums;

enum PaymentFrequency: string
{
    case MONTHLY = 'monthly';
    case BIWEEKLY = 'biweekly';

    public function label(): string
    {
        return match ($this) {
            self::MONTHLY => 'Mensual',
            self::BIWEEKLY => 'Quincenal',
        };
    }
}
