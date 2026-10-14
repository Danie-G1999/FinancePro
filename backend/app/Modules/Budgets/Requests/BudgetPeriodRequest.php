<?php

namespace App\Modules\Budgets\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'period' => ['required', 'date_format:Y-m'],
        ];
    }
}
