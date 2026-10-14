<?php

namespace App\Modules\Income\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'account_id' => $this->account_id,

            'account_name' => $this->account?->name,

            'recurring_income_id' => $this->recurring_income_id,

            'recurring_label' => $this->recurringIncome
                ? $this->recurringIncome->type->label() . ' automático'
                : null,

            'description' => $this->description,

            'amount' => $this->amount,

            'currency' => $this->account?->currency,

            'received_at' => $this->received_at?->toDateString(),

            'notes' => $this->notes,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
