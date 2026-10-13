<?php

namespace App\Modules\Expenses\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'account_id' => $this->account_id,

            'account_name' => $this->account?->name,

            'debt_id' => $this->debt_id,

            'debt_name' => $this->debt?->name,

            'description' => $this->description,

            'amount' => $this->amount,

            'currency' => $this->account?->currency,

            'spent_at' => $this->spent_at?->toDateString(),

            'notes' => $this->notes,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
