<?php

namespace App\Modules\Goals\Models;

use App\Models\User;
use App\Modules\Expenses\Models\Expense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    protected $table = 'goals';

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'currency',
        'target_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'target_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Aportes: gastos registrados con destino a esta meta. */
    public function contributions(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function savedAmount(): float
    {
        return (float) ($this->contributions_sum_amount
            ?? $this->contributions()->sum('amount'));
    }

    public function remainingAmount(): float
    {
        return max(
            0,
            round((float) $this->target_amount - $this->savedAmount(), 2)
        );
    }

    public function contributionsCount(): int
    {
        return (int) ($this->contributions_count
            ?? $this->contributions()->count());
    }
}
