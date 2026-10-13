<?php

namespace App\Modules\Transactions\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,

            'type' => $this->type,

            'type_label' => $this->type === 'income' ? 'Ingreso' : 'Gasto',

            'description' => $this->description,

            'amount' => number_format((float) $this->amount, 2, '.', ''),

            'currency' => $this->currency,

            'date' => Carbon::parse($this->date)->toDateString(),

            'account_id' => (int) $this->account_id,

            'account_name' => $this->account_name,

            'debt_id' => $this->debt_id ? (int) $this->debt_id : null,

            'debt_name' => $this->debt_name,

            'notes' => $this->notes,

            'created_at' => Carbon::parse($this->created_at)->toISOString(),
        ];
    }
}
