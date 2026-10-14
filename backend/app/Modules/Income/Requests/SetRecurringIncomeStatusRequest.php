<?php

namespace App\Modules\Income\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetRecurringIncomeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
