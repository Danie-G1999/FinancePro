<?php

namespace App\Modules\Expenses\Models;

use App\Models\User;
use App\Modules\Accounts\Models\Account;
use App\Modules\Debts\Models\Debt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'user_id',
        'account_id',
        'debt_id',
        'description',
        'amount',
        'spent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }
}
