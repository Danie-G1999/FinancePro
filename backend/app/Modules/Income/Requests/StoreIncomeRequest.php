<?php

namespace App\Modules\Income\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')
                    ->where('user_id', $this->user()->id)
                    ->where('is_active', true),
            ],

            'description' => ['required', 'string', 'max:150'],

            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],

            'received_at' => ['required', 'date_format:Y-m-d'],

            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.exists' => 'La cuenta seleccionada no es válida.',
        ];
    }
}
