<?php

namespace App\Modules\Income\Resources;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringIncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'account_id' => $this->account_id,

            'account_name' => $this->account?->name,

            'currency' => $this->account?->currency,

            'description' => $this->description,

            'amount' => $this->amount,

            'type' => $this->type?->value,

            'type_label' => $this->type?->label(),

            'day_of_month' => $this->day_of_month,

            'start_date' => $this->start_date?->toDateString(),

            'next_payment_date' => $this->nextPaymentDate(CarbonImmutable::today())?->toDateString(),

            'is_active' => $this->is_active,

            'notes' => $this->notes,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
