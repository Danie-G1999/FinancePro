<?php

namespace App\Modules\Auth\Resources;

use App\Modules\Legal\Services\LegalService;
use App\Modules\Settings\Resources\UserSettingsResource;
use App\Modules\Settings\Services\UserSettingsService;
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
            'google_linked' => $this->google_id !== null,
            'legal' => app(LegalService::class)->statusFor($this->resource),
            'settings' => (new UserSettingsResource(
                app(UserSettingsService::class)->forUser($this->id)
            ))->resolve($request),
            'created_at' => $this->created_at,
        ];
    }
}
