<?php

namespace App\Modules\Categories\Models;

use App\Models\User;
use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Expenses\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $table = 'expense_categories';

    protected $fillable = [
        'user_id',
        'name',
        'budget_type',
        'icon',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'budget_type' => BudgetType::class,
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
