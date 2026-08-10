<?php

namespace App\Modules\Accounts\Enums;

enum AccountType: string
{
    case CASH = 'cash';
    case BANK = 'bank';
    case SAVINGS = 'savings';
    case CHECKING = 'checking';
    case DIGITAL_WALLET = 'digital_wallet';
    case CREDIT_CARD = 'credit_card';
}
