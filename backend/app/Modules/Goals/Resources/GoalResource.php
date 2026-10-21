<?php

namespace App\Modules\Goals\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $target = (float) $this->target_amount;
        $saved = $this->savedAmount();

        return [
            'id' => $this->id,

            'name' => $this->name,

            'target_amount' => $this->target_amount,

            'currency' => $this->currency,

            'currency_locked' => $this->currencyLocked(),

            'target_date' => $this->target_date?->toDateString(),

            'description' => $this->description,

            'saved_amount' => number_format($saved, 2, '.', ''),

            'remaining_amount' => number_format($this->remainingAmount(), 2, '.', ''),

            'progress_percent' => $target > 0
                ? min(100, round($saved / $target * 100, 1))
                : 0,

            'contributions_count' => $this->contributionsCount(),

            'is_completed' => $this->remainingAmount() <= 0,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
