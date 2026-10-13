<?php

namespace App\Modules\Profile\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,

            'last_name' => $this->last_name,

            'birth_date' => $this->birth_date?->toDateString(),

            'email' => $this->email,

            'phone' => $this->phone,

            'address' => $this->address,

            'photo_url' => $this->photoUrl(),

            'employment_type' => $this->employment_type?->value,

            'employment_type_label' => $this->employment_type?->label(),

            'contributes_social_security' => $this->contributes_social_security,

            'contract_type' => $this->contract_type?->value,

            'contract_type_label' => $this->contract_type?->label(),

            'monthly_income' => $this->monthly_income,

            'family_reference_name' => $this->family_reference_name,

            'family_reference_relationship' => $this->family_reference_relationship,

            'family_reference_phone' => $this->family_reference_phone,

            'is_completed' => $this->exists,

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
