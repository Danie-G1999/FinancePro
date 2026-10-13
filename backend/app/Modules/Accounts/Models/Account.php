<?php

namespace App\Modules\Accounts\Models;

use App\Modules\Accounts\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Income\Models\Income;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Accounts\database\factories\AccountFactory;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'institution',
        'initial_balance',
        'currency',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'initial_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function currentBalance(): float
    {
        $incomes = $this->incomes_sum_amount
            ?? $this->incomes()->sum('amount');

        $expenses = $this->expenses_sum_amount
            ?? $this->expenses()->sum('amount');

        return round(
            (float) $this->initial_balance
                + (float) $incomes
                - (float) $expenses,
            2
        );
    }
}
