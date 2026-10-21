<?php

namespace App\Modules\Settings\Models;

use App\Models\User;
use App\Modules\Settings\Enums\BudgetMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    /** Valores con los que arranca cada usuario. */
    public const DEFAULTS = [
        'currency' => 'COP',
        'budget_method' => '50_30_20',
        'budget_need_percent' => 50,
        'budget_want_percent' => 30,
        'budget_savings_percent' => 20,
        'auto_logout_inactive' => true,
        'inactivity_timeout_minutes' => 5,
    ];

    protected $fillable = [
        'user_id',
        'currency',
        'budget_method',
        'budget_need_percent',
        'budget_want_percent',
        'budget_savings_percent',
        'auto_logout_inactive',
        'inactivity_timeout_minutes',
    ];

    protected function casts(): array
    {
        return [
            'budget_method' => BudgetMethod::class,
            'budget_need_percent' => 'integer',
            'budget_want_percent' => 'integer',
            'budget_savings_percent' => 'integer',
            'auto_logout_inactive' => 'boolean',
            'inactivity_timeout_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
