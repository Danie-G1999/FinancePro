<?php

namespace App\Modules\Profile\Enums;

enum ContractType: string
{
    case FIXED_TERM = 'fixed_term';
    case INDEFINITE = 'indefinite';

    public function label(): string
    {
        return match ($this) {
            self::FIXED_TERM => 'Término fijo',
            self::INDEFINITE => 'Indefinido',
        };
    }
}
