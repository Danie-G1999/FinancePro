<?php

namespace App\Modules\Expenses\Models;

use App\Models\User;
use App\Modules\Accounts\Models\Account;
use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Budgets\Enums\CostType;
use App\Modules\Categories\Models\ExpenseCategory;
use App\Modules\Debts\Models\Debt;
use App\Modules\Goals\Models\Goal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'user_id',
        'account_id',
        'debt_id',
        'goal_id',
        'expense_category_id',
        'budget_type',
        'cost_type',
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
            'budget_type' => BudgetType::class,
            'cost_type' => CostType::class,
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

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
