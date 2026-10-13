<?php

namespace App\Modules\Debts\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DebtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'type' => $this->type?->value,

            'type_label' => $this->type?->label(),

            'creditor' => $this->creditor,

            'total_amount' => $this->total_amount,

            'installments' => $this->installments,

            'payment_frequency' => $this->payment_frequency?->value,

            'payment_frequency_label' => $this->payment_frequency?->label(),

            'payment_day' => $this->payment_day,

            'second_payment_day' => $this->second_payment_day,

            'payment_days_label' => $this->second_payment_day
                ? "{$this->payment_day} y {$this->second_payment_day}"
                : (string) $this->payment_day,

            'interest_rate' => $this->interest_rate,

            'monthly_payment' => $this->monthly_payment,

            'currency' => $this->currency,

            'due_date' => $this->due_date?->toDateString(),

            'description' => $this->description,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
