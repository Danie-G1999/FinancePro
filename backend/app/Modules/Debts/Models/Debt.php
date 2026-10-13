<?php

namespace App\Modules\Debts\Models;

use App\Models\User;
use App\Modules\Debts\Enums\DebtType;
use App\Modules\Debts\Enums\PaymentFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Debt extends Model
{
    protected $table = 'debts';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'creditor',
        'total_amount',
        'installments',
        'payment_frequency',
        'payment_day',
        'second_payment_day',
        'interest_rate',
        'monthly_payment',
        'currency',
        'due_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => DebtType::class,
            'total_amount' => 'decimal:2',
            'installments' => 'integer',
            'payment_frequency' => PaymentFrequency::class,
            'payment_day' => 'integer',
            'second_payment_day' => 'integer',
            'interest_rate' => 'decimal:2',
            'monthly_payment' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
