<?php

namespace App\Modules\Income\Models;

use App\Models\User;
use App\Modules\Accounts\Models\Account;
use App\Modules\Income\Enums\RecurringIncomeType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringIncome extends Model
{
    protected $table = 'recurring_incomes';

    protected $fillable = [
        'user_id',
        'account_id',
        'description',
        'amount',
        'type',
        'day_of_month',
        'start_date',
        'last_generated_period',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'type' => RecurringIncomeType::class,
            'day_of_month' => 'integer',
            'start_date' => 'date',
            'is_active' => 'boolean',
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

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /** Fecha de pago del mes dado; en meses cortos se usa el último día. */
    public function paymentDateFor(CarbonImmutable $month): CarbonImmutable
    {
        $month = $month->startOfMonth();

        return $month->day(min($this->day_of_month, $month->daysInMonth));
    }

    /** Próxima fecha de pago posterior a hoy, o null si está en pausa. */
    public function nextPaymentDate(CarbonImmutable $today): ?CarbonImmutable
    {
        if (! $this->is_active) {
            return null;
        }

        $startDate = CarbonImmutable::parse($this->start_date);
        $month = $today->startOfMonth();

        for ($i = 0; $i < 3; $i++) {
            $date = $this->paymentDateFor($month->addMonths($i));

            if ($date > $today && $date >= $startDate) {
                return $date;
            }
        }

        return null;
    }
}
