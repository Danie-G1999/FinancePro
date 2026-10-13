<?php

namespace App\Modules\Accounts\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'type' => $this->type?->value,

            'type_label' => $this->type?->label(),

            'institution' => $this->institution,

            'initial_balance' => $this->initial_balance,

            'balance' => number_format(
                $this->currentBalance(),
                2,
                '.',
                ''
            ),

            'currency' => $this->currency,

            'is_active' => $this->is_active,

            'description' => $this->description,

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
