<?php

namespace App\Modules\Settings\Resources;

use App\Modules\Settings\Services\UserSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $percents = app(UserSettingsService::class)->budgetPercents($this->user_id);

        return [
            'currency' => $this->currency,

            'budget_method' => $this->budget_method?->value,

            'budget_method_label' => $this->budget_method?->label(),

            // Lo que guardó el usuario para el método personalizado.
            'custom_percents' => [
                'need' => $this->budget_need_percent,
                'want' => $this->budget_want_percent,
                'savings' => $this->budget_savings_percent,
            ],

            // Lo que se aplica hoy (50/30/20 o los personalizados).
            'budget_percents' => $percents,

            'auto_logout_inactive' => $this->auto_logout_inactive,

            'inactivity_timeout_minutes' => $this->inactivity_timeout_minutes,

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
