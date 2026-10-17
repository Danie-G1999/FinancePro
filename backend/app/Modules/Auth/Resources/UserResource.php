<?php

namespace App\Modules\Auth\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'photo_url' => $this->profile?->photoUrl(),
            'email_verified_at' => $this->email_verified_at,
            'onboarding_completed' => $this->onboarding_completed_at !== null,
            'created_at' => $this->created_at,
        ];
    }
}
