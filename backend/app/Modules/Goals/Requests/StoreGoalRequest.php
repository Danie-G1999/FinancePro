<?php

namespace App\Modules\Goals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],

            'target_amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],

            'currency' => ['required', 'string', 'size:3'],

            'target_date' => ['nullable', 'date_format:Y-m-d'],

            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
