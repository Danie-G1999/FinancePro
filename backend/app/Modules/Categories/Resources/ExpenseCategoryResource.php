<?php

namespace App\Modules\Categories\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'budget_type' => $this->budget_type?->value,

            'budget_type_label' => $this->budget_type?->label(),

            'budget_type_percent' => $this->budget_type?->percent(),

            'icon' => $this->icon,

            'color' => $this->color,

            'is_active' => $this->is_active,

            'expenses_count' => (int) ($this->expenses_count ?? 0),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
